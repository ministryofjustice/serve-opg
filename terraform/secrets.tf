resource "aws_secretsmanager_secret" "slack_url" {
  name        = "serve_slack_url"
  description = "Slack url and token for notifications"
  tags        = local.default_tags
}

data "aws_secretsmanager_secret_version" "slack_url" {
  secret_id = aws_secretsmanager_secret.slack_url.id
}

resource "aws_secretsmanager_secret" "behat_password" {
  name        = "behat_password"
  description = "Behat password for non local envs"
  tags        = local.default_tags
}