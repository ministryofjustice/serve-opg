resource "aws_iam_role" "enhanced_monitoring" {
  name               = "rds-enhanced-monitoring"
  assume_role_policy = data.aws_iam_policy_document.enhanced_monitoring.json
}

resource "aws_iam_role_policy_attachment" "enhanced_monitoring" {
  policy_arn = "arn:aws:iam::aws:policy/service-role/AmazonRDSEnhancedMonitoringRole"
  role       = aws_iam_role.enhanced_monitoring.name
}

data "aws_iam_policy_document" "enhanced_monitoring" {
  statement {
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      identifiers = ["monitoring.rds.amazonaws.com"]
      type        = "Service"
    }
  }
}

# Breakglass role notify when assumed

resource "aws_cloudwatch_event_rule" "breakglass_assume" {
  name        = "breakglass-assume-role"
  description = "Trigger when someone assumes the breakglass IAM role"

  event_pattern = jsonencode({
    "source" : ["aws.sts"],
    "detail-type" : ["AWS API Call via CloudTrail"],
    "detail" : {
      "eventSource" : ["sts.amazonaws.com"],
      "eventName"   : ["AssumeRole"],
      "requestParameters" : {
        "roleArn" : [{
          "prefix" : "arn:aws:iam::933639921819:role/breakglass" # Production breakglass role
        }]
      }
    }
  })
}

# Send to SNS topic when breakglass role is assumed
resource "aws_cloudwatch_event_target" "breakglass_to_sns" {
  rule      = aws_cloudwatch_event_rule.breakglass_assume.name
  target_id = "send-to-slack"
  arn       = aws_sns_topic.serve_slack_notifications.arn

  input_transformer {
    input_paths = {
      "user" = "$.detail.userIdentity.arn"
    }

    input_template = <<EOT
{
  "type": "breakglass",
  "user": <user>
}
EOT
  }
}

# Allow EventBridge to publish to SNS topic
resource "aws_sns_topic_policy" "allow_eventbridge_publish" {
  arn = aws_sns_topic.serve_slack_notifications.arn

  policy = jsonencode({
    Version = "2012-10-17",
    Statement = [
      {
        Sid       = "AllowEventBridgePublish",
        Effect    = "Allow",
        Principal = {
          Service = "events.amazonaws.com"
        },
        Action   = "sns:Publish",
        Resource = aws_sns_topic.serve_slack_notifications.arn
      }
    ]
  })
}