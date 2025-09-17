# Notify Slack
resource "aws_lambda_function" "serve_opg_notify_slack" {
  function_name = "serve-opg-slack"
  role          = aws_iam_role.serve_opg_lambda_exec.arn
  handler       = "slack_notify.handler"
  runtime       = "python3.12"

  filename         = data.archive_file.slack_notify.output_path
  source_code_hash = data.archive_file.slack_notify.output_base64sha256
}

data "archive_file" "slack_notify" {
  type        = "zip"
  source_file = "${path.module}/../../scripts/slack_notify/slack_notify.py"
  output_path = "${path.module}/../../scripts/slack_notify/slack_notify.zip"
}

resource "aws_cloudwatch_log_group" "serve_opg_notify_slack" {
  name              = "/aws/lambda/${aws_lambda_function.serve_opg_notify_slack.function_name}"
  retention_in_days = 14
}


resource "aws_iam_role" "serve_opg_lambda_exec" {
  name               = "serve-opg-slack-exec"
  assume_role_policy = data.aws_iam_policy_document.serve_opg_lambda_assume.json
}


data "aws_iam_policy_document" "serve_opg_lambda_assume" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      type        = "Service"
      identifiers = ["lambda.amazonaws.com"]
    }
  }
}

data "aws_iam_policy_document" "serve_opg_notify_lambda" {
  statement {
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue",
    ]
    resources = [
      aws_secretsmanager_secret.slack_webhooks.arn,
    ]
  }

  statement {
    sid    = "AllowLambdaLogging"
    effect = "Allow"
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents",
      "logs:DescribeLogStreams",
    ]
    resources = [
      aws_cloudwatch_log_group.serve_opg_notify_slack.arn,
      "${aws_cloudwatch_log_group.serve_opg_notify_slack.arn}:*",
    ]
  }

  statement {
    sid    = "SNS"
    effect = "Allow"
    actions = [
      "SNS:Subscribe",
      "SNS:Receive",
    ]
    resources = [
      aws_sns_topic.serve_slack_notifications.arn
    ]
  }

  statement {
    sid    = "SnsDecryptKms"
    effect = "Allow"
    actions = [
      "kms:Decrypt"
    ]
    resources = [
      module.logs_kms.target_key_arn
    ]
  }

  statement {
    sid    = "SecretDecryptKms"
    effect = "Allow"
    actions = [
      "kms:Decrypt"
    ]
    resources = [
      module.logs_kms.target_key_arn
    ]
  }

}

resource "aws_iam_policy" "serve_opg_notify_lambda" {
  name        = "serve-opg-slack-secret-access"
  description = "Policy for the Slack Notify Lambda"
  policy      = data.aws_iam_policy_document.serve_opg_notify_lambda.json
}

resource "aws_iam_role_policy_attachment" "serve_opg_notify_lambda_attach" {
  role       = aws_iam_role.serve_opg_lambda_exec.name
  policy_arn = aws_iam_policy.serve_opg_notify_lambda.arn
}
