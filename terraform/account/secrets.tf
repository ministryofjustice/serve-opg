resource "aws_secretsmanager_secret" "slack_url" {
  name        = "serve_slack_url"
  description = "Slack url and token for notifications"
  tags        = local.default_tags
}

data "aws_secretsmanager_secret_version" "slack_url" {
  secret_id = aws_secretsmanager_secret.slack_url.id
}

resource "aws_secretsmanager_secret" "cloud9_users" {
  name        = "cloud9-users"
  description = "Serve team Cloud9 users"
  tags        = local.default_tags
}

#data "aws_secretsmanager_secret_version" "cloud9_users" {
#  secret_id = aws_secretsmanager_secret.cloud9_users.id
#}
