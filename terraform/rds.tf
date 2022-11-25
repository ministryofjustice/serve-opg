resource "aws_rds_cluster" "serve_opg" {
  master_password              = data.aws_secretsmanager_secret_version.database_password.secret_string
  master_username              = "serveopgadmin"
  engine                       = "aurora-postgresql"
  engine_version               = local.postgres_engine_version
  skip_final_snapshot          = local.rds_deletion_protection ? false : true
  final_snapshot_identifier    = "serve-opg-${terraform.workspace}"
  database_name                = "serve_opg"
  db_subnet_group_name         = aws_db_subnet_group.database.name
  vpc_security_group_ids       = [aws_security_group.database.id]
  backup_retention_period      = 7
  deletion_protection          = local.rds_deletion_protection
  tags                         = local.default_tags
  allow_major_version_upgrade  = true
  apply_immediately            = local.rds_deletion_protection ? false : true
  preferred_backup_window      = "05:15-05:45"
  preferred_maintenance_window = "mon:05:50-mon:06:20"

  lifecycle {
    prevent_destroy = true
  }
}

resource "aws_rds_cluster_instance" "cluster_instances" {
  count                        = 2
  identifier_prefix            = "serve-opg-"
  cluster_identifier           = aws_rds_cluster.serve_opg.id
  instance_class               = "db.r5.large"
  engine                       = aws_rds_cluster.serve_opg.engine
  engine_version               = aws_rds_cluster.serve_opg.engine_version
  performance_insights_enabled = true
  monitoring_role_arn          = aws_iam_role.enhanced_monitoring.arn
  monitoring_interval          = 60
  tags                         = local.default_tags

  lifecycle {
    create_before_destroy = true
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
  subnet_ids = aws_subnet.private.*.id
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
  from_port                = aws_rds_cluster.serve_opg.port
  to_port                  = aws_rds_cluster.serve_opg.port
  security_group_id        = aws_security_group.database.id
  source_security_group_id = aws_security_group.ecs_service.id
  type                     = "ingress"
}

resource "aws_security_group_rule" "database_tcp_out" {
  protocol                 = "tcp"
  from_port                = aws_rds_cluster.serve_opg.port
  to_port                  = aws_rds_cluster.serve_opg.port
  security_group_id        = aws_security_group.database.id
  source_security_group_id = aws_security_group.ecs_service.id
  type                     = "egress"
}

