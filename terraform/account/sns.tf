# Slack Alerts SNS Topic
resource "aws_sns_topic" "serve_slack_notifications" {
  name              = "serve-slack-notifications"
  kms_master_key_id = aws_kms_key.serve_sns.arn

  tags = merge(
    local.default_tags,
    { Name = "serve-slack-notifications-${local.account.name}" },
  )
}

# Add Lambda as a consumer to the SNS Topic
resource "aws_sns_topic_subscription" "serve_slack_notifications_lambda" {
  topic_arn = aws_sns_topic.serve_slack_notifications.arn
  protocol  = "lambda"
  endpoint  = aws_lambda_function.serve_opg_notify_slack.arn
}

resource "aws_lambda_permission" "serve_slack_notifications_allow" {
  statement_id  = "AllowExecutionFromSNS"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.serve_opg_notify_slack.function_name
  principal     = "sns.amazonaws.com"
  source_arn    = aws_sns_topic.serve_slack_notifications.arn
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.serve_opg_notify_slack
    ]
  }
}
