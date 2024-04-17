resource "aws_cloudwatch_metric_alarm" "alb_errors_24h" {
  alarm_name          = "${local.environment}-5xx-errors-alb"
  statistic           = "Sum"
  metric_name         = "HTTPCode_ELB_5XX_Count"
  comparison_operator = "GreaterThanThreshold"
  threshold           = 0
  period              = 86400
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  namespace           = "AWS/ApplicationELB"
  alarm_actions       = [data.aws_sns_topic.alert.arn]

  dimensions = {
    LoadBalancer = aws_lb.frontend.arn_suffix
    TargetGroup  = aws_lb_target_group.frontend.arn_suffix
  }

  treat_missing_data = "notBreaching"
}

resource "aws_cloudwatch_metric_alarm" "response_time" {
  alarm_name          = "${local.environment}-response-time"
  statistic           = "Average"
  metric_name         = "TargetResponseTime"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 5
  period              = 600
  datapoints_to_alarm = 3
  evaluation_periods  = 3
  namespace           = "AWS/ApplicationELB"
  alarm_actions       = [data.aws_sns_topic.alert.arn]

  dimensions = {
    LoadBalancer = aws_lb.frontend.arn_suffix
    TargetGroup  = aws_lb_target_group.frontend.arn_suffix
  }
}

resource "aws_route53_health_check" "availability-front" {
  fqdn              = aws_route53_record.serve.fqdn
  resource_path     = "/health-check"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  tags              = merge(local.default_tags, { Name = "availability-front" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability-front" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-front"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  datapoints_to_alarm = 3
  threshold           = 1
  period              = 60
  evaluation_periods  = 3
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.alert_us_east.arn]
  tags                = local.default_tags

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-front.id
  }
}

resource "aws_route53_health_check" "availability-service" {
  fqdn              = aws_route53_record.serve.fqdn
  resource_path     = "/health-check/service"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  tags              = merge(local.default_tags, { Name = "availability-service" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability-service" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-service"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  datapoints_to_alarm = 3
  threshold           = 1
  period              = 60
  evaluation_periods  = 3
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.alert_us_east.arn]
  tags                = local.default_tags

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-service.id
  }
}

resource "aws_route53_health_check" "availability-dependencies" {
  fqdn              = aws_route53_record.serve.fqdn
  resource_path     = "/health-check/dependencies"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  tags              = merge(local.default_tags, { Name = "availability-dependencies" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability-dependencies" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-dependencies"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  datapoints_to_alarm = 5
  threshold           = 1
  period              = 60
  evaluation_periods  = 5
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.alert_us_east.arn]
  tags                = local.default_tags

  dimensions = {
    HealthCheckId = aws_route53_health_check.availability-dependencies.id
  }
}

resource "aws_cloudwatch_metric_alarm" "errors_24h" {
  alarm_name          = "${local.environment}-5xx-errors"
  statistic           = "Sum"
  metric_name         = "HTTPCode_Target_5XX_Count"
  comparison_operator = "GreaterThanThreshold"
  threshold           = 0
  period              = 86400
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  namespace           = "AWS/ApplicationELB"
  alarm_actions       = [data.aws_sns_topic.alert.arn]

  dimensions = {
    LoadBalancer = aws_lb.frontend.arn_suffix
    TargetGroup  = aws_lb_target_group.frontend.arn_suffix
  }

  treat_missing_data = "notBreaching"
}

resource "aws_cloudwatch_metric_alarm" "availability_24h" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-24"
  statistic           = "Minimum"
  metric_name         = "HealthCheckStatus"
  comparison_operator = "LessThanThreshold"
  threshold           = 1
  period              = 300
  datapoints_to_alarm = 1
  evaluation_periods  = 288
  namespace           = "AWS/Route53"
  alarm_actions       = [data.aws_sns_topic.alert_us_east.arn]

  dimensions = {
    HealthCheckId = aws_route53_health_check.homepage.id
  }
}

resource "aws_cloudwatch_log_metric_filter" "sirius_login_errors" {
  name           = "${local.environment}-serve-sirius-login-errors"
  pattern        = "\"ERROR\" \"publicapi\" \"Request ->\""
  log_group_name = aws_cloudwatch_log_group.serve.name

  metric_transformation {
    name          = "${local.environment}-serve-sirius-login-errors"
    namespace     = "Server/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "sirius_login_errors" {
  alarm_name          = "${local.environment}-serve-sirius-login-errors"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.sirius_login_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.sirius_login_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alert.arn]
  tags                = local.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "sirius_unavailable_errors" {
  name           = "${local.environment}-serve-sirius-unavailable-errors"
  pattern        = "\"NotFoundHttpException\" \"No route found for\" \"/api/passphrase\""
  log_group_name = aws_cloudwatch_log_group.serve.name

  metric_transformation {
    name          = "${local.environment}-serve-sirius-unavailable-errors"
    namespace     = "Server/Error"
    value         = "1"
    default_value = "0"
  }
}

resource "aws_cloudwatch_metric_alarm" "sirius_unavailable_errors" {
  alarm_name          = "${local.environment}-serve-sirius-unavailable-errors"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.sirius_unavailable_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.sirius_unavailable_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.alert.arn]
  tags                = local.default_tags
}
