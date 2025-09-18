# Slack Alerts SNS Topic
resource "aws_sns_topic" "serve_slack_notifications" {
  name              = "serve-slack-notifications"
  kms_master_key_id = module.sns_kms.target_key_arn

  tags = merge(
    local.default_tags,
    { Name = "serve-slack-notifications-${local.account.name}" },
  )
}

resource "aws_sns_topic_policy" "serve_slack_notifications" {
  arn    = aws_sns_topic.serve_slack_notifications.arn
  policy = data.aws_iam_policy_document.serve_slack_notifications.json
}

data "aws_iam_policy_document" "serve_slack_notifications" {
  statement {
    sid    = "Publish SNS"
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["cloudwatch.amazonaws.com"]
    }

    actions   = ["sns:Publish"]
    resources = [aws_sns_topic.serve_slack_notifications.arn]
  }
}

# Add Lambda as a consumer to the SNS Topic
resource "aws_sns_topic_subscription" "serve_slack_notifications_lambda" {
  topic_arn = aws_sns_topic.serve_slack_notifications.arn
  protocol  = "lambda"
  endpoint  = aws_lambda_function.serve_opg_notify_slack.arn
}

resource "aws_sns_topic_subscription" "serve_slack_notifications_lambda_global" {
  provider = aws.us-east-1

  topic_arn = aws_sns_topic.serve_slack_notifications_global.arn
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

resource "aws_lambda_permission" "serve_slack_global_notifications_allow" {
  statement_id  = "AllowExecutionFromSNS"
  action        = "lambda:InvokeFunction"
  function_name = aws_lambda_function.serve_opg_notify_slack.function_name
  principal     = "sns.amazonaws.com"
  source_arn    = aws_sns_topic.serve_slack_notifications_global.arn
  lifecycle {
    replace_triggered_by = [
      aws_lambda_function.serve_opg_notify_slack
    ]
  }
}

# Slack Alerts Global Topic
#trivy:ignore:avd-aws-0095 - Can't do cross region SNS encryption
resource "aws_sns_topic" "serve_slack_notifications_global" {
  provider     = aws.us-east-1
  name         = "serve-slack-notifications-global"
  display_name = "Serve Slack Notifications Global"
  tags = merge(
    local.default_tags,
    { Name = "serve-slack-notifications-global" },
  )
}

resource "aws_sns_topic_policy" "serve_slack_notifications_global" {
  arn    = aws_sns_topic.serve_slack_notifications_global.arn
  policy = data.aws_iam_policy_document.serve_slack_notifications_global.json
}

data "aws_iam_policy_document" "serve_slack_notifications_global" {
  statement {
    sid    = "Publish SNS"
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["cloudwatch.amazonaws.com"]
    }

    actions   = ["sns:Publish"]
    resources = [aws_sns_topic.serve_slack_notifications_global.arn]
  }
}
