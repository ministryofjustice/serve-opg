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

resource "aws_secretsmanager_secret" "slack_webhooks" {
  name        = "slack-webhooks"
  description = "Slack webhooks for notifications"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "public_api_password" {
  name        = "sirius_public_api_password"
  description = "Sirius public API password"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "notification_api_key" {
  name        = "notification_api_key"
  description = "Notification API key"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "os_places_api_key" {
  name        = "os_places_api_key"
  description = "OS Places API key"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "symfony_app_secret" {
  name        = "symfony_app_secret"
  description = "Symfony application secret"
  tags        = local.default_tags
}

resource "aws_secretsmanager_secret" "database_password" {
  name        = "database_password"
  description = "Database password"
  tags        = local.default_tags
}

# TODO Remove Import Blocks
data "aws_secretsmanager_secret" "public_api_password" {
  name = "sirius_public_api_password"
}

import {
  to = aws_secretsmanager_secret.public_api_password
  id = data.aws_secretsmanager_secret.public_api_password.arn
}

data "aws_secretsmanager_secret" "notification_api_key" {
  name = "notification_api_key"
}

import {
  to = aws_secretsmanager_secret.notification_api_key
  id = data.aws_secretsmanager_secret.notification_api_key.arn
}

data "aws_secretsmanager_secret" "os_places_api_key" {
  name = "os_places_api_key"
}

import {
  to = aws_secretsmanager_secret.os_places_api_key
  id = data.aws_secretsmanager_secret.os_places_api_key.arn
}

data "aws_secretsmanager_secret" "symfony_app_secret" {
  name = "symfony_app_secret"
}

import {
  to = aws_secretsmanager_secret.symfony_app_secret
  id = data.aws_secretsmanager_secret.symfony_app_secret.arn
}

data "aws_secretsmanager_secret" "database_password" {
  name = "database_password"
}

import {
  to = aws_secretsmanager_secret.database_password
  id = data.aws_secretsmanager_secret.database_password.arn
}
