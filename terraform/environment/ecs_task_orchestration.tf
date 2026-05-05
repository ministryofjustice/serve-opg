# Restore Task
module "restore" {
  source = "./modules/task"
  name   = "restore"

  cluster_name          = aws_ecs_cluster.serve_opg.name
  cpu                   = 2048
  memory                = 4096
  container_definitions = "[${local.restore}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution.arn
  subnet_ids            = data.aws_subnet.application[*].id
  task_role_arn         = aws_iam_role.orchestration.arn
  security_group_id     = aws_security_group.orchestration.id
}

locals {
  restore = jsonencode(
    {
      name    = "restore",
      command = ["./restore.sh"],
      image   = "311462405659.dkr.ecr.eu-west-1.amazonaws.com/serve-opg/orchestration:${var.APP_VERSION}",
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.serve.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "restore"
        }
      },
      secrets = [
        {
          name      = "POSTGRES_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        }
      ],
      environment = local.orchestration_variables
    }
  )
}

# Backup Task
module "backup" {
  source = "./modules/task"
  name   = "backup"

  cluster_name          = aws_ecs_cluster.serve_opg.name
  cpu                   = 2048
  memory                = 4096
  container_definitions = "[${local.backup}]"
  tags                  = local.default_tags
  environment           = local.environment
  execution_role_arn    = aws_iam_role.execution.arn
  subnet_ids            = data.aws_subnet.application[*].id
  task_role_arn         = aws_iam_role.orchestration.arn
  security_group_id     = aws_security_group.orchestration.id
}

locals {
  backup = jsonencode(
    {
      name    = "backup",
      command = ["./backup.sh"],
      image   = "311462405659.dkr.ecr.eu-west-1.amazonaws.com/serve-opg/orchestration:${var.APP_VERSION}",
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.serve.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "backup"
        }
      },
      secrets = [
        {
          name      = "POSTGRES_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        }
      ],
      environment = local.orchestration_variables
    }
  )
}

# Orchestration Tasks Security Group
resource "aws_security_group" "orchestration" {
  name        = "orchestration-${local.environment}"
  vpc_id      = local.account.use_new_network ? data.aws_vpc.main.id : data.aws_vpc.vpc.id
  tags        = local.default_tags
  description = "orchestration ${local.environment}"

  lifecycle {
    create_before_destroy = true
  }
}

resource "aws_security_group_rule" "orchestration_to_database" {
  count                    = local.account.use_new_network ? 1 : 0
  protocol                 = "tcp"
  from_port                = aws_rds_cluster.cluster_serverless.port
  to_port                  = aws_rds_cluster.cluster_serverless.port
  security_group_id        = aws_security_group.orchestration.id
  source_security_group_id = aws_security_group.database.id
  type                     = "egress"
}

resource "aws_security_group_rule" "orchestration_egress_ecr" {
  count                    = local.account.use_new_network ? 1 : 0
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = aws_security_group.orchestration.id
  source_security_group_id = data.aws_security_group.ecr_endpoint.id
  type                     = "egress"
}

resource "aws_security_group_rule" "orchestration_egress_ecr_api" {
  count                    = local.account.use_new_network ? 1 : 0
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = aws_security_group.orchestration.id
  source_security_group_id = data.aws_security_group.ecr_api_endpoint.id
  type                     = "egress"
}

resource "aws_security_group_rule" "orchestration_egress_secrets" {
  count                    = local.account.use_new_network ? 1 : 0
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = aws_security_group.orchestration.id
  source_security_group_id = data.aws_security_group.secrets_endpoint.id
  type                     = "egress"
}

resource "aws_security_group_rule" "orchestration_egress_logs" {
  count                    = local.account.use_new_network ? 1 : 0
  protocol                 = "tcp"
  from_port                = 443
  to_port                  = 443
  security_group_id        = aws_security_group.orchestration.id
  source_security_group_id = data.aws_security_group.logs_endpoint.id
  type                     = "egress"
}

resource "aws_security_group_rule" "orchestration_egress_s3" {
  count             = local.account.use_new_network ? 1 : 0
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
  security_group_id = aws_security_group.orchestration.id
  prefix_list_ids   = [data.aws_vpc_endpoint.s3_endpoint.prefix_list_id]
  type              = "egress"
}

locals {
  orchestration_variables = [
    {
      name  = "POSTGRES_DATABASE",
      value = aws_rds_cluster.cluster_serverless.database_name
    },
    {
      name  = "POSTGRES_HOST",
      value = aws_rds_cluster.cluster_serverless.endpoint
    },
    {
      name  = "POSTGRES_PORT",
      value = tostring(aws_rds_cluster.cluster_serverless.port)
    },
    {
      name  = "POSTGRES_USER",
      value = aws_rds_cluster.cluster_serverless.master_username
    },
    {
      name  = "S3_PREFIX",
      value = local.environment
    },
    {
      name  = "S3_BUCKET",
      value = aws_s3_bucket.orchestration.bucket
    }
  ]
}

# VPC Endpoints
data "aws_security_group" "ecr_endpoint" {
  tags   = { Name = "ecr_endpoint" }
  vpc_id = data.aws_vpc.main.id
}

data "aws_security_group" "logs_endpoint" {
  tags   = { Name = "logs_endpoint" }
  vpc_id = data.aws_vpc.main.id
}

data "aws_vpc_endpoint" "s3_endpoint" {
  service_name = "com.amazonaws.eu-west-1.s3"
  vpc_id       = data.aws_vpc.main.id
}

data "aws_security_group" "secrets_endpoint" {
  tags   = { Name = "secrets_endpoint" }
  vpc_id = data.aws_vpc.main.id
}

data "aws_security_group" "ecr_api_endpoint" {
  tags   = { Name = "ecr_api_endpoint" }
  vpc_id = data.aws_vpc.main.id
}
