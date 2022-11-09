resource "aws_cloudwatch_metric_alarm" "alb_errors_24h" {
  alarm_name          = "5xxErrorsALB"
  statistic           = "Sum"
  metric_name         = "HTTPCode_ELB_5XX_Count"
  comparison_operator = "GreaterThanThreshold"
  threshold           = 0
  period              = 86400
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  namespace           = "AWS/ApplicationELB"
  alarm_actions       = [aws_sns_topic.alert.arn]

  dimensions = {
    LoadBalancer = aws_lb.loadbalancer.arn_suffix
    TargetGroup  = aws_lb_target_group.frontend.arn_suffix
  }

  treat_missing_data = "notBreaching"
}

resource "aws_cloudwatch_metric_alarm" "response_time" {
  alarm_name          = "ResponseTime"
  statistic           = "Average"
  metric_name         = "TargetResponseTime"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 5
  period              = 600
  datapoints_to_alarm = 3
  evaluation_periods  = 3
  namespace           = "AWS/ApplicationELB"
  alarm_actions       = [aws_sns_topic.alert.arn]

  dimensions = {
    LoadBalancer = aws_lb.loadbalancer.arn_suffix
    TargetGroup  = aws_lb_target_group.frontend.arn_suffix
  }
}

resource "aws_cloudwatch_metric_alarm" "errors_24h" {
  alarm_name          = "5xxErrors"
  statistic           = "Sum"
  metric_name         = "HTTPCode_Target_5XX_Count"
  comparison_operator = "GreaterThanThreshold"
  threshold           = 0
  period              = 86400
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  namespace           = "AWS/ApplicationELB"
  alarm_actions       = [aws_sns_topic.alert.arn]

  dimensions = {
    LoadBalancer = aws_lb.loadbalancer.arn_suffix
    TargetGroup  = aws_lb_target_group.frontend.arn_suffix
  }

  treat_missing_data = "notBreaching"
}

resource "aws_cloudwatch_metric_alarm" "availability_24h" {
  provider            = aws.us-east-1
  alarm_name          = "availability"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  threshold           = 1
  period              = 300
  datapoints_to_alarm = 1
  evaluation_periods  = 288
  namespace           = "AWS/Route53"
  alarm_actions       = [aws_sns_topic.alert_us_east.arn]

  dimensions = {
    HealthCheckId = aws_route53_health_check.homepage.id
  }
}

resource "aws_cloudwatch_log_metric_filter" "sirius_login_errors" {
  name           = "ServeSiriusLoginErrors.${terraform.workspace}"
  pattern        = "\"ERROR\" \"publicapi\" \"Request ->\""
  log_group_name = aws_cloudwatch_log_group.frontend.name

  metric_transformation {
    name          = "ServeSiriusLoginErrors.${terraform.workspace}"
    namespace     = "Server/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "sirius_login_errors" {
  alarm_name          = "ServeSiriusLoginErrors.${terraform.workspace}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.sirius_login_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.sirius_login_errors.metric_transformation[0].namespace
  alarm_actions       = [aws_sns_topic.alert.arn]
  tags                = local.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "sirius_unavailable_errors" {
  name           = "ServeSiriusUnavailableErrors.${terraform.workspace}"
  pattern        = "\"NotFoundHttpException\" \"No route found for\" \"/api/passphrase\""
  log_group_name = aws_cloudwatch_log_group.frontend.name

  metric_transformation {
    name          = "ServeSiriusUnavailableErrors.${terraform.workspace}"
    namespace     = "Server/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "sirius_unavailable_errors" {
  alarm_name          = "ServeSiriusUnavailableErrors.${terraform.workspace}"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.sirius_unavailable_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.sirius_unavailable_errors.metric_transformation[0].namespace
  alarm_actions       = [aws_sns_topic.alert.arn]
  tags                = local.default_tags
}
