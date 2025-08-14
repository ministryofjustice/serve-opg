resource "aws_ecs_task_definition" "frontend" {
  family                   = "frontend-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.app},${local.web}]"
  task_role_arn            = aws_iam_role.task.arn
  execution_role_arn       = aws_iam_role.execution.arn
}

locals {
  web = jsonencode(
    {
      cpu         = 0,
      essential   = true,
      image       = "311462405659.dkr.ecr.eu-west-1.amazonaws.com/serve-opg/web:${var.APP_VERSION}",
      mountPoints = [],
      name        = "web",
      portMappings = [{
        containerPort = 80,
        hostPort      = 80,
        protocol      = "tcp"
      }],
      volumesFrom = [],
      healthCheck = {
        command = [
          "CMD-SHELL",
          "curl -f http://localhost:80/health-check || exit 1"
        ],
        interval = 30,
        timeout  = 10,
        retries  = 3
      },
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.serve.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "frontend.web"
        }
      },
      environment = [
        {
          name  = "TIMEOUT",
          value = "60"
        }
      ]
    }
  )

  app = jsonencode(
    {
      cpu         = 0,
      essential   = true,
      image       = "311462405659.dkr.ecr.eu-west-1.amazonaws.com/serve-opg/app:${var.APP_VERSION}",
      mountPoints = [],
      name        = "app",
      portMappings = [
        {
          containerPort = 9000,
          hostPort      = 9000,
          protocol      = "tcp"
        }
      ],
      healthCheck = {
        command = [
          "CMD",
          "/usr/local/bin/health-check.sh"
        ],
        interval = 30,
        timeout  = 10,
        retries  = 3
      },
      volumesFrom = [],
      logConfiguration = {
        logDriver = "awslogs",
        options = {
          awslogs-group         = aws_cloudwatch_log_group.serve.name,
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "frontend.app"
        }
      },
      secrets = [
        {
          name      = "NOTIFICATION_API_KEY",
          valueFrom = data.aws_secretsmanager_secret.notification_api_key.arn
        },
        {
          name      = "OS_PLACES_API_KEY",
          valueFrom = data.aws_secretsmanager_secret.os_places_api_key.arn
        },
        {
          name      = "SIRIUS_PUBLIC_API_EMAIL",
          valueFrom = data.aws_secretsmanager_secret.sirius_api_email.arn
        },
        {
          name      = "SIRIUS_PUBLIC_API_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.public_api_password.arn
        },
        {
          name      = "APP_SECRET",
          valueFrom = data.aws_secretsmanager_secret.symfony_app_secret.arn
        },
        {
          name      = "BEHAT_PASSWORD",
          valueFrom = data.aws_secretsmanager_secret.behat_password.arn
        },
        {
          name      = "DC_DB_PASS",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        }
      ],
      environment = [
        {
          name  = "DC_DB_HOST",
          value = aws_rds_cluster.cluster_serverless.endpoint
        },
        {
          name  = "DC_DB_PORT",
          value = tostring(aws_rds_cluster.cluster_serverless.port)
        },
        {
          name  = "DC_DB_NAME",
          value = aws_rds_cluster.cluster_serverless.database_name
        },
        {
          name  = "DC_DB_USER",
          value = aws_rds_cluster.cluster_serverless.master_username
        },
        {
          name  = "DC_DB_SSL",
          value = "verify-full"
        },
        {
          name  = "TIMEOUT",
          value = "60"
        },
        {
          name  = "DC_S3_BUCKET_NAME",
          value = aws_s3_bucket.bucket.bucket
        },
        {
          name  = "DC_ASSETS_VERSION",
          value = var.APP_VERSION
        },
        {
          name  = "APP_VERSION",
          value = var.APP_VERSION
        },
        {
          name  = "DC_BEHAT_CONTROLLER_ENABLED",
          value = tostring(local.account.behat_controller)
        },
        {
          name  = "DC_SIRIUS_URL",
          value = local.account.sirius_api
        },
        {
          name  = "SIRIUS_S3_BUCKET_NAME",
          value = local.account.sirius_bucket
        },
        {
          name  = "SIRIUS_KMS_KEY_ARN",
          value = local.sirius_key_alias_arn
        },
        {
          "name" : "DYNAMODB_ENDPOINT",
          value = "https://dynamodb.eu-west-1.amazonaws.com:443"
        },
        {
          "name" : "DC_S3_ENDPOINT",
          value = "https://s3-eu-west-1.amazonaws.com"
        },
        {
          name  = "FIXTURES_ENABLED",
          value = local.account.fixtures_enabled
        },
        {
          name  = "USE_EVENT_BUS",
          value = local.account.use_event_bus
        },
        {
          name  = "ENVIRONMENT_NAME",
          value = local.account.account_name
        },
        {
          name  = "WORKSPACE",
          value = local.environment
        }
      ]
    }
  )
}
