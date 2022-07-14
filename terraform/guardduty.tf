resource "aws_guardduty_detector" "detector" {
  enable = true
}

resource "aws_sns_topic" "guardduty-topic" {
  name = "GuardDuty-to-PagerDuty"
}

resource "aws_cloudwatch_event_rule" "guardduty-events" {
  name        = "opg-guardduty"
  description = "OPG GuardDuty Finding Events"
  event_pattern = jsonencode({
    "source" = [
      "aws.guardduty"
    ],
    "detail-type" = [
      "GuardDuty Finding"
    ]
  })
}

resource "aws_cloudwatch_event_target" "guardduty-target-sns" {
  rule      = aws_cloudwatch_event_rule.guardduty-events.name
  target_id = "SendToSNS"
  arn       = aws_sns_topic.guardduty-topic.arn
}
