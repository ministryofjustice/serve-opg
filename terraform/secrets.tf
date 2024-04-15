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

resource "aws_secretsmanager_secret" "sirius_api_email" {
  name        = "sirius_api_email_${local.environment}"
  description = "Sirius API email for ${local.environment}"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "cloud9_users" {
  name        = "cloud9-users"
  description = "Serve team Cloud9 users"
  tags        = local.default_tags
}

data "aws_secretsmanager_secret_version" "cloud9_users" {
  secret_id = aws_secretsmanager_secret.cloud9_users.id
}

data "aws_secretsmanager_secret" "sirius_api_email" {
  name = "sirius_api_email_${local.environment}"
}

data "aws_secretsmanager_secret_version" "sirius_api_email" {
  secret_id = data.aws_secretsmanager_secret.sirius_api_email.id
}

data "aws_secretsmanager_secret" "public_api_password" {
  name = data.aws_secretsmanager_secret_version.sirius_api_email.secret_string
}

data "aws_secretsmanager_secret" "notification_api_key" {
  name = "notification_api_key"
}

data "aws_secretsmanager_secret" "os_places_api_key" {
  name = "os_places_api_key"
}

data "aws_secretsmanager_secret" "symfony_app_secret" {
  name = "symfony_app_secret"
}

data "aws_secretsmanager_secret" "database_password" {
  name = "database_password"
}

data "aws_secretsmanager_secret_version" "database_password" {
  secret_id = data.aws_secretsmanager_secret.database_password.id
}
