resource "aws_sns_topic" "alert" {
  name         = "${local.service}-${local.environment}-app-alert"
  display_name = "${local.default_tags["application"]} ${local.capitalized_environment} App Alert"
  tags         = local.default_tags
}

resource "aws_sns_topic" "alert_us_east" {
  provider     = aws.us-east-1
  name         = "${local.service}-${local.environment}-alert"
  display_name = "${local.default_tags["application"]} ${local.capitalized_environment} Alert"
  tags         = local.default_tags
}

data "aws_sns_topic" "alert_custom" {
  name = "custom_cloudwatch_alarms"
}

module "notify_slack" {
  source = "github.com/terraform-aws-modules/terraform-aws-notify-slack.git?ref=v6.7.0"

  sns_topic_name   = aws_sns_topic.alert.name
  create_sns_topic = false

  lambda_function_name = "notify-slack"

  cloudwatch_log_group_retention_in_days = 14

  slack_webhook_url = data.aws_secretsmanager_secret_version.slack_url.secret_string
  slack_channel     = local.environment == "production" ? "#opg-digideps-team" : "#serve-opg"
  slack_username    = "aws"
  slack_emoji       = ":warning:"

  tags = local.default_tags
}

resource "aws_sns_topic_subscription" "lambda_subscription" {
  topic_arn = data.aws_sns_topic.alert_custom.arn
  protocol  = "lambda"
  endpoint  = module.notify_slack.notify_slack_lambda_function_arn
}

resource "aws_lambda_permission" "allow_custom_sns" {
  statement_id  = "AllowExecutionFromCustomSNS"
  action        = "lambda:InvokeFunction"
  function_name = module.notify_slack.notify_slack_lambda_function_name
  principal     = "sns.amazonaws.com"
  source_arn    = data.aws_sns_topic.alert_custom.arn
}

module "notify_slack_us-east-1" {
  source = "github.com/terraform-aws-modules/terraform-aws-notify-slack.git?ref=v6.7.0"

  providers = {
    aws = aws.us-east-1
  }

  sns_topic_name   = aws_sns_topic.alert_us_east.name
  create_sns_topic = false
  create           = local.environment != "development"

  lambda_function_name = "notify-slack"

  iam_role_name_prefix = "us-east-1"

  cloudwatch_log_group_retention_in_days = 14

  slack_webhook_url = data.aws_secretsmanager_secret_version.slack_url.secret_string
  slack_channel     = local.environment == "production" ? "#opg-digideps-team" : "#serve-opg"
  slack_username    = "aws"
  slack_emoji       = ":warning:"

  tags = local.default_tags
}

# Notify Slack for GitHub Actions

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

data "aws_iam_policy_document" "serve_opg_lambda_secret_access" {
  statement {
    effect = "Allow"
    actions = [
      "secretsmanager:GetSecretValue",
    ]
    resources = [
      aws_secretsmanager_secret.slack_webhooks.arn,
    ]
  }
}

data "aws_iam_policy_document" "serve_opg_lambda_logging" {
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
}

resource "aws_iam_role" "serve_opg_lambda_exec" {
  name               = "serve-opg-slack-exec"
  assume_role_policy = data.aws_iam_policy_document.serve_opg_lambda_assume.json
}

resource "aws_iam_policy" "serve_opg_lambda_secret_access" {
  name        = "serve-opg-slack-secret-access"
  description = "Allow Lambda to read Slack webhooks secret"
  policy      = data.aws_iam_policy_document.serve_opg_lambda_secret_access.json
}

resource "aws_iam_policy" "serve_opg_lambda_logging" {
  name        = "serve-opg-slack-logging"
  description = "Allow Lambda to write logs to its CloudWatch group"
  policy      = data.aws_iam_policy_document.serve_opg_lambda_logging.json
}

resource "aws_iam_role_policy_attachment" "serve_opg_lambda_secret_access_attach" {
  role       = aws_iam_role.serve_opg_lambda_exec.name
  policy_arn = aws_iam_policy.serve_opg_lambda_secret_access.arn
}

resource "aws_iam_role_policy_attachment" "serve_opg_lambda_logging_attach" {
  role       = aws_iam_role.serve_opg_lambda_exec.name
  policy_arn = aws_iam_policy.serve_opg_lambda_logging.arn
}

data "archive_file" "slack_notify" {
  type        = "zip"
  source_file = "${path.module}/../../scripts/slack_notify/slack_notify.py"
  output_path = "${path.module}/../../scripts/slack_notify/slack_notify.zip"
}

resource "aws_lambda_function" "serve_opg_notify_slack" {
  function_name = "serve-opg-slack"
  role          = aws_iam_role.serve_opg_lambda_exec.arn
  handler       = "slack_notify.handler"
  runtime       = "python3.12"

  filename         = data.archive_file.slack_notify.output_path
  source_code_hash = data.archive_file.slack_notify.output_base64sha256
}

resource "aws_cloudwatch_log_group" "serve_opg_notify_slack" {
  name              = "/aws/lambda/${aws_lambda_function.serve_opg_notify_slack.function_name}"
  retention_in_days = 14
}
