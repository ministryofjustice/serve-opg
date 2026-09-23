resource "aws_secretsmanager_secret" "pagerduty_integration_key" {
  count = local.account.name == "production" ? 1 : 0

  name        = "pagerduty-integration-key"
  description = "PagerDuty Amazon CloudWatch integration key"
  kms_key_id  = module.secrets_kms.target_key_arn
  tags        = local.default_tags
}

data "aws_secretsmanager_secret_version" "pagerduty_integration_key" {
  count = local.pagerduty_is_enabled ? 1 : 0

  secret_id = aws_secretsmanager_secret.pagerduty_integration_key[0].id
}

locals {
  pagerduty_is_enabled = local.account.name == "production" && local.account.pagerduty_enabled
  pagerduty_endpoint   = local.pagerduty_is_enabled ? "https://events.pagerduty.com/integration/${trimspace(data.aws_secretsmanager_secret_version.pagerduty_integration_key[0].secret_string)}/enqueue" : null
}

resource "aws_sns_topic_subscription" "pagerduty_notifications" {
  count = local.pagerduty_is_enabled ? 1 : 0

  topic_arn              = aws_sns_topic.serve_slack_notifications.arn
  protocol               = "https"
  endpoint               = local.pagerduty_endpoint
  endpoint_auto_confirms = true
  raw_message_delivery   = false
}

resource "aws_sns_topic_subscription" "pagerduty_notifications_global" {
  provider = aws.us-east-1
  count    = local.pagerduty_is_enabled ? 1 : 0

  topic_arn              = aws_sns_topic.serve_slack_notifications_global.arn
  protocol               = "https"
  endpoint               = local.pagerduty_endpoint
  endpoint_auto_confirms = true
  raw_message_delivery   = false
}

resource "aws_sns_topic_subscription" "pagerduty_guardduty_findings" {
  count = local.pagerduty_is_enabled ? 1 : 0

  topic_arn              = data.aws_sns_topic.guardduty_findings.arn
  protocol               = "https"
  endpoint               = local.pagerduty_endpoint
  endpoint_auto_confirms = true
  raw_message_delivery   = false
}
