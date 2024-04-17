data "aws_kms_key" "sirius" {
  key_id   = "alias/${local.account.sirius_key_alias}"
  provider = aws.sirius
}

resource "aws_ecs_task_definition" "frontend" {
  family                   = "frontend-${local.environment}"
  requires_compatibilities = ["FARGATE"]
  network_mode             = "awsvpc"
  cpu                      = 512
  memory                   = 1024
  container_definitions    = "[${local.app},${local.web}]"
  task_role_arn            = aws_iam_role.task_role.arn
  execution_role_arn       = aws_iam_role.execution_role.arn
}

# TODO - Change to env specific after change to sirius side
resource "aws_iam_role" "task_role" {
  name               = "frontend"
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
}

data "aws_iam_policy_document" "task_role_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "task_role" {
  policy = data.aws_iam_policy_document.task_role.json
  role   = aws_iam_role.task_role.name
}

data "aws_iam_policy_document" "task_role" {
  statement {
    sid       = "KmsAccess"
    effect    = "Allow"
    actions   = ["kms:*"]
    resources = [data.aws_kms_key.sirius.arn]
  }

  statement {
    sid       = "DynamoSessionStorage"
    effect    = "Allow"
    actions   = ["dynamodb:*"]
    resources = ["*"]
  }

  statement {
    sid    = "S3DocumentStorage"
    effect = "Allow"

    actions = [
      "s3:List*",
      "s3:AbortMultipartUpload",
      "s3:DeleteObject",
      "s3:DeleteObject*",
      "s3:GetObject",
      "s3:GetObject*",
      "s3:PutObject",
      "s3:PutObject*",
      "s3:RestoreObject",
      "s3:GetEncryptionConfiguration",
    ]

    resources = [
      aws_s3_bucket.bucket.arn,
      "${aws_s3_bucket.bucket.arn}/*",
      "arn:aws:s3:::${local.account.sirius_bucket}",
      "arn:aws:s3:::${local.account.sirius_bucket}/*",
    ]
  }

  statement {
    sid    = "RetrieveSecrets"
    effect = "Allow"

    actions = [
      "secretsmanager:GetSecretValue",
      "secretsmanager:DescribeSecret",
    ]

    resources = [
      data.aws_secretsmanager_secret.public_api_password.arn,
      data.aws_secretsmanager_secret.sirius_api_email.arn,
      data.aws_secretsmanager_secret.notification_api_key.arn,
      data.aws_secretsmanager_secret.os_places_api_key.arn,
    ]
  }
}

resource "aws_iam_role" "execution_role" {
  name               = "execution_role"
  assume_role_policy = data.aws_iam_policy_document.execution_role_assume_policy.json
}

data "aws_iam_policy_document" "execution_role_assume_policy" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["ecs-tasks.amazonaws.com"]
      type        = "Service"
    }
  }
}

resource "aws_iam_role_policy" "execution_role" {
  policy = data.aws_iam_policy_document.execution_role.json
  role   = aws_iam_role.execution_role.id
}

data "aws_iam_policy_document" "execution_role" {
  statement {
    effect    = "Allow"
    resources = ["*"]

    actions = [
      "ecr:GetAuthorizationToken",
      "ecr:BatchCheckLayerAvailability",
      "ecr:GetDownloadUrlForLayer",
      "ecr:BatchGetImage",
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "ssm:GetParameters",
      "secretsmanager:GetSecretValue",
    ]
  }
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
          awslogs-group         = "/ecs/serve-opg",
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "frontend"
        }
      },
      environment = [{
        name  = "APP_HOST",
        value = "127.0.0.1"
        },
        {
          name  = "APP_PORT",
          value = "9000"
        },
        {
          name  = "TIMEOUT",
          value = "60"
      }]
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
          awslogs-group         = "/ecs/serve-opg",
          awslogs-region        = "eu-west-1",
          awslogs-stream-prefix = "frontend"
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
          valueFrom = aws_secretsmanager_secret.behat_password.arn
        },
        {
          name      = "DC_DB_PASS",
          valueFrom = data.aws_secretsmanager_secret.database_password.arn
        }
      ],
      environment = [
        {
          name  = "DC_GTM",
          value = local.account.dc_gtm
        },
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
          value = data.aws_kms_key.sirius.arn
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
        }
      ]
    }
  )
}
