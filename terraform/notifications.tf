resource "aws_sns_topic" "alert" {
  name         = "${local.service}-${terraform.workspace}-app-alert"
  display_name = "${local.default_tags["application"]} ${local.capitalized_environment} App Alert"
  tags         = local.default_tags
}

resource "aws_sns_topic" "alert_us_east" {
  provider     = aws.us-east-1
  name         = "${local.service}-${terraform.workspace}-alert"
  display_name = "${local.default_tags["application"]} ${local.capitalized_environment} Alert"
  tags         = local.default_tags
}

module "notify_slack" {
  source = "github.com/terraform-aws-modules/terraform-aws-notify-slack.git?ref=v2.4.0"

  sns_topic_name   = aws_sns_topic.alert.name
  create_sns_topic = false

  lambda_function_name = "notify-slack"

  cloudwatch_log_group_retention_in_days = 14

  slack_webhook_url = data.aws_secretsmanager_secret_version.slack_url.secret_string
  slack_channel     = "#serve-opg"
  slack_username    = "aws"
  slack_emoji       = ":warning:"

  tags = local.default_tags
}

module "notify_slack_us-east-1" {
  source = "github.com/terraform-aws-modules/terraform-aws-notify-slack.git?ref=v2.4.0"

  providers = {
    aws = aws.us-east-1
  }

  sns_topic_name   = aws_sns_topic.alert_us_east.name
  create_sns_topic = false
  create           = terraform.workspace != "development"

  lambda_function_name = "notify-slack"

  cloudwatch_log_group_retention_in_days = 14

  slack_webhook_url = data.aws_secretsmanager_secret_version.slack_url.secret_string
  slack_channel     = terraform.workspace == "production" ? "#opg-digideps-team" : "#opg-digideps-devs"
  slack_username    = "aws"
  slack_emoji       = ":warning:"

  tags = local.default_tags
}
