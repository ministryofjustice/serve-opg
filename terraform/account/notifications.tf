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
