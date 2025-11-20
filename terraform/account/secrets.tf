resource "aws_secretsmanager_secret" "behat_password" {
  name        = "behat_password"
  description = "Behat password for non local envs"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "sirius_api_email" {
  name        = "sirius_api_email_${local.environment}"
  description = "Sirius API email for ${local.environment}"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "slack_url" {
  name        = "serve_slack_url"
  description = "Slack url and token for notifications"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "slack_webhooks" {
  name        = "slack-webhooks"
  description = "Slack webhooks for notifications"
  tags        = local.default_tags
}
