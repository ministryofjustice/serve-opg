data "aws_secretsmanager_secret" "sirius_api_email" {
  name = "sirius_api_email_${local.account.account_name}"
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

data "aws_secretsmanager_secret" "behat_password" {
  name = "behat_password"
}
