locals {
  availability_zones        = ["eu-west-1a", "eu-west-1b", "eu-west-1c"]
}

# See the following link for further information
# https://docs.aws.amazon.com/kms/latest/developerguide/key-policies.html
data "aws_iam_policy_document" "cloudwatch_kms" {
  statement {
    sid       = "Enable Root account permissions on Key"
    effect    = "Allow"
    actions   = ["kms:*"]
    resources = ["*"]

    principals {
      type = "AWS"
      identifiers = [
        "arn:aws:iam::${data.aws_caller_identity.current.account_id}:root",
      ]
    }
  }

  statement {
    sid       = "Allow Key to be used for Encryption"
    effect    = "Allow"
    resources = ["*"]
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey",
    ]

    principals {
      type = "Service"
      identifiers = [
        "logs.${data.aws_region.current.name}.amazonaws.com",
        "events.amazonaws.com"
      ]
    }
  }
}

resource "aws_kms_key" "cloudwatch_logs" {
  description             = "Serve cloudwatch logs for ${terraform.workspace}"
  deletion_window_in_days = 10
  enable_key_rotation     = true
  policy                  = data.aws_iam_policy_document.cloudwatch_kms.json
}

resource "aws_cloudwatch_log_group" "api_cluster" {
  name              = "/aws/rds/cluster/serve-opg-${terraform.workspace}/postgresql"
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  retention_in_days = 180
  tags              = local.default_tags
}

data "aws_kms_key" "rds" {
  key_id = "alias/aws/rds"
}

resource "aws_rds_cluster" "cluster_serverless" {
  cluster_identifier                  = "serve-opg-${terraform.workspace}-cluster"
  apply_immediately                   = local.account.deletion_protection ? false : true
  availability_zones                  = local.availability_zones
  backup_retention_period             = 14
  copy_tags_to_snapshot               = true
  database_name                       = "serve_opg"
  db_subnet_group_name                = aws_db_subnet_group.database.name
  deletion_protection                 = local.account.deletion_protection ? true : false
  engine                              = "aurora-postgresql"
  engine_version                      = local.account.postgres_version
  engine_mode                         = "provisioned"
  final_snapshot_identifier           = "serve-opg-${terraform.workspace}-final-snapshot"
  kms_key_id                          = data.aws_kms_key.rds.arn
  master_username                     = "serveopgadmin"
  master_password                     = data.aws_secretsmanager_secret_version.database_password.secret_string
  preferred_backup_window             = "05:15-05:45"
  preferred_maintenance_window        = "mon:05:50-mon:06:20"
  storage_encrypted                   = true
  skip_final_snapshot                 = local.account.deletion_protection ? false : true
  vpc_security_group_ids              = [aws_security_group.database.id]
  tags                                = local.default_tags
  iam_database_authentication_enabled = false

  serverlessv2_scaling_configuration {
    min_capacity = 0.5
    max_capacity = 16
  }
  depends_on = [aws_cloudwatch_log_group.api_cluster]
}

resource "aws_rds_cluster_instance" "serverless_instances" {
  count                           = 2
  cluster_identifier              = aws_rds_cluster.cluster_serverless.cluster_identifier
  apply_immediately               = local.account.deletion_protection ? false : true
  auto_minor_version_upgrade      = false
  db_subnet_group_name            = aws_db_subnet_group.database.name
  depends_on                      = [aws_rds_cluster.cluster_serverless]
  engine                          = aws_rds_cluster.cluster_serverless.engine
  engine_version                  = aws_rds_cluster.cluster_serverless.engine_version
  identifier                      = "serve-opg-${terraform.workspace}-${count.index}"
  instance_class                  = "db.serverless"
  monitoring_interval             = 30
  monitoring_role_arn             = "arn:aws:iam::${local.account.account_id}:role/rds-enhanced-monitoring"
  performance_insights_enabled    = true
  performance_insights_kms_key_id = data.aws_kms_key.rds.arn
  publicly_accessible             = false
  tags                            = local.default_tags

  timeouts {
    create = "180m"
    update = "90m"
    delete = "90m"
  }
}

resource "aws_iam_role" "enhanced_monitoring" {
  name               = "rds-enhanced-monitoring"
  assume_role_policy = data.aws_iam_policy_document.enhanced_monitoring.json
}

resource "aws_iam_role_policy_attachment" "enhanced_monitoring" {
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonRDSEnhancedMonitoringRole"
  role       = aws_iam_role.enhanced_monitoring.name
}

data "aws_iam_policy_document" "enhanced_monitoring" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["monitoring.rds.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_db_subnet_group" "database" {
  subnet_ids = aws_subnet.private[*].id
  tags       = local.default_tags
}

resource "aws_security_group" "database" {
  name   = "database"
  vpc_id = aws_default_vpc.default.id
  tags   = local.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "database_tcp_in" {
  protocol                 = "tcp"
  from_port                = aws_rds_cluster.cluster_serverless.port
  to_port                  = aws_rds_cluster.cluster_serverless.port
  security_group_id        = aws_security_group.database.id
  source_security_group_id = aws_security_group.ecs_service.id
  type                     = "ingress"
}

resource "aws_security_group_rule" "c9_to_db_in" {
  protocol                 = "tcp"
  from_port                = aws_rds_cluster.cluster_serverless.port
  to_port                  = aws_rds_cluster.cluster_serverless.port
  security_group_id        = aws_security_group.database.id
  source_security_group_id = data.aws_security_group.cloud9.id
  type                     = "ingress"
}

resource "aws_security_group_rule" "database_tcp_out" {
  protocol                 = "tcp"
  from_port                = aws_rds_cluster.cluster_serverless.port
  to_port                  = aws_rds_cluster.cluster_serverless.port
  security_group_id        = aws_security_group.database.id
  source_security_group_id = aws_security_group.ecs_service.id
  type                     = "egress"
}
