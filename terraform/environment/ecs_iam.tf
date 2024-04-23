# ===== Task role =====
resource "aws_iam_role" "task" {
  name               = "frontend-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.task_role_assume_policy.json
}

resource "aws_iam_role_policy" "task" {
  policy = data.aws_iam_policy_document.task_role.json
  role   = aws_iam_role.task.name
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

# ===== Task Execution Role =====
resource "aws_iam_role" "execution" {
  name               = "execution-role-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.execution_role_assume_policy.json
}

resource "aws_iam_role_policy" "execution" {
  policy = data.aws_iam_policy_document.execution_role.json
  role   = aws_iam_role.execution.id
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
