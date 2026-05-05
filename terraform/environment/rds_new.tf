resource "aws_cloudwatch_log_group" "rds" {
  name              = "/aws/rds/cluster/serve-${local.environment}/postgresql"
  kms_key_id        = aws_kms_key.cloudwatch_logs.arn
  retention_in_days = 180
  tags              = local.default_tags
}

resource "aws_rds_cluster" "cluster" {
  cluster_identifier                  = "serve-${local.environment}-cluster"
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
  final_snapshot_identifier           = "serve-${local.environment}-final-snapshot"
  kms_key_id                          = data.aws_kms_alias.rds_encryption_key.target_key_arn
  master_username                     = "serveopgadmin"
  master_password                     = data.aws_secretsmanager_secret_version.database_password.secret_string
  preferred_backup_window             = "05:15-05:45"
  preferred_maintenance_window        = "mon:05:50-mon:06:20"
  storage_encrypted                   = true
  skip_final_snapshot                 = local.account.deletion_protection ? false : true
  vpc_security_group_ids              = [aws_security_group.database.id]
  tags                                = local.default_tags
  iam_database_authentication_enabled = true

  serverlessv2_scaling_configuration {
    min_capacity = local.environment == "production" ? 0.5 : 0
    max_capacity = 8
  }
  depends_on = [aws_cloudwatch_log_group.rds]
}

resource "aws_rds_cluster_instance" "instances" {
  count                           = local.account.rds_instance_count
  cluster_identifier              = aws_rds_cluster.cluster.cluster_identifier
  apply_immediately               = local.account.deletion_protection ? false : true
  auto_minor_version_upgrade      = false
  db_subnet_group_name            = aws_db_subnet_group.database.name
  depends_on                      = [aws_rds_cluster.cluster]
  engine                          = aws_rds_cluster.cluster.engine
  engine_version                  = aws_rds_cluster.cluster.engine_version
  identifier                      = "serve-${local.environment}-${count.index}"
  instance_class                  = "db.serverless"
  monitoring_interval             = 30
  monitoring_role_arn             = "arn:aws:iam::${local.account.account_id}:role/rds-enhanced-monitoring"
  performance_insights_enabled    = true
  performance_insights_kms_key_id = data.aws_kms_alias.rds_encryption_key.target_key_arn
  ca_cert_identifier              = "rds-ca-rsa2048-g1"
  publicly_accessible             = false
  tags                            = local.default_tags

  timeouts {
    create = "180m"
    update = "90m"
    delete = "90m"
  }
}

resource "aws_db_subnet_group" "rds" {
  subnet_ids = data.aws_subnet.data[*].id
  tags       = local.default_tags
}

resource "aws_security_group" "rds" {
  name        = "database-sg-${local.environment}"
  description = "database ${local.environment}"
  vpc_id      = data.aws_vpc.main.id
  tags        = local.default_tags

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "rds_tcp_in" {
  protocol                 = "tcp"
  from_port                = aws_rds_cluster.cluster.port
  to_port                  = aws_rds_cluster.cluster.port
  security_group_id        = aws_security_group.rds.id
  source_security_group_id = aws_security_group.ecs_service.id
  type                     = "ingress"
}

resource "aws_security_group_rule" "rds_tcp_out" {
  protocol                 = "tcp"
  from_port                = aws_rds_cluster.cluster.port
  to_port                  = aws_rds_cluster.cluster.port
  security_group_id        = aws_security_group.rds.id
  source_security_group_id = aws_security_group.ecs_service.id
  type                     = "egress"
}
