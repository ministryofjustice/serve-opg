resource "aws_cloudwatch_log_metric_filter" "waf_blocked_requests" {
  name           = "WAFBlockedRequests.${local.account.name}"
  log_group_name = aws_cloudwatch_log_group.waf_web_acl.name
  pattern        = "{ $.action = \"BLOCK\" }"

  metric_transformation {
    name          = "WAFBlockedRequests.${local.account.name}"
    namespace     = "Serve/WAF"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "waf_blocked_requests_high_volume" {
  alarm_name        = "${local.account.name}-waf-high-block-volume"
  alarm_description = "Triggers when AWS WAF blocks 100 or more requests within 5 minutes, indicating a potential ddos or bot attack, or serious problem with the waf."

  namespace   = aws_cloudwatch_log_metric_filter.waf_blocked_requests.metric_transformation[0].namespace
  metric_name = aws_cloudwatch_log_metric_filter.waf_blocked_requests.metric_transformation[0].name

  statistic           = "Sum"
  period              = 300
  evaluation_periods  = 1
  datapoints_to_alarm = 1

  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 100

  alarm_actions      = [aws_sns_topic.serve_slack_notifications.arn]
  ok_actions         = local.pagerduty_is_enabled ? [aws_sns_topic.serve_slack_notifications.arn] : []
  treat_missing_data = "notBreaching"
  actions_enabled    = true

  tags = local.default_tags
}
