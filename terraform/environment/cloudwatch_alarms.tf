# ===== Application Loadbalancer Alarms =====
resource "aws_cloudwatch_metric_alarm" "loadbalancer_response_time" {
  alarm_name          = "${local.environment}-response-time"
  alarm_description   = "Serve high response times recorded on the loadbalancer"
  statistic           = "Average"
  metric_name         = "TargetResponseTime"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  threshold           = 5
  period              = 300
  datapoints_to_alarm = 3
  evaluation_periods  = 3
  namespace           = "AWS/ApplicationELB"
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]

  dimensions = {
    LoadBalancer = aws_lb.frontend.arn_suffix
    TargetGroup  = aws_lb_target_group.frontend.arn_suffix
  }
}

resource "aws_cloudwatch_metric_alarm" "loadbalancer_app_errors" {
  alarm_name          = "${local.environment}-5xx-errors"
  alarm_description   = "Serve 5XX errors recorded on the loadbalancer"
  statistic           = "Sum"
  metric_name         = "HTTPCode_Target_5XX_Count"
  comparison_operator = "GreaterThanThreshold"
  threshold           = 1
  period              = 60
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  namespace           = "AWS/ApplicationELB"
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]

  dimensions = {
    LoadBalancer = aws_lb.frontend.arn_suffix
    TargetGroup  = aws_lb_target_group.frontend.arn_suffix
  }

  treat_missing_data = "notBreaching"
}

#===== Healthcheck Alarms =====
resource "aws_route53_health_check" "availability_frontend" {
  fqdn              = aws_route53_record.serve.fqdn
  resource_path     = "/health-check"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  regions           = ["us-east-1", "eu-west-1", "us-west-1"]
  tags              = merge(local.default_tags, { Name = "availability-front" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability_frontend" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-frontend"
  alarm_description   = "Serve route53 health-checks for route /health-check have failed"
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
    HealthCheckId = aws_route53_health_check.availability_frontend.id
  }
}

resource "aws_route53_health_check" "availability_service" {
  fqdn              = aws_route53_record.serve.fqdn
  resource_path     = "/health-check/service"
  port              = 443
  type              = "HTTPS"
  failure_threshold = 1
  request_interval  = 30
  measure_latency   = true
  regions           = ["us-east-1", "eu-west-1", "us-west-1"]
  tags              = merge(local.default_tags, { Name = "availability-service" }, )
}

resource "aws_cloudwatch_metric_alarm" "availability_service" {
  provider            = aws.us-east-1
  alarm_name          = "${local.environment}-availability-service"
  alarm_description   = "Serve route53 health-checks for route /health-check/service have failed"
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
    HealthCheckId = aws_route53_health_check.availability_service.id
  }
}

# TODO Fix the /dependencies endpoint!
#moved {
#  from = aws_route53_health_check.availability-dependencies
#  to   = aws_route53_health_check.availability_dependencies
#}
#
#resource "aws_route53_health_check" "availability_dependencies" {
#  fqdn              = aws_route53_record.serve.fqdn
#  resource_path     = "/health-check/dependencies"
#  port              = 443
#  type              = "HTTPS"
#  failure_threshold = 1
#  request_interval  = 30
#  measure_latency   = true
#  regions           = ["us-east-1", "eu-west-1", "us-west-1"]
#  tags              = merge(local.default_tags, { Name = "availability-dependencies" }, )
#}
#
#moved {
#  from = aws_cloudwatch_metric_alarm.availability-dependencies
#  to   = aws_cloudwatch_metric_alarm.availability_dependencies
#}
#
#resource "aws_cloudwatch_metric_alarm" "availability_dependencies" {
#  provider            = aws.us-east-1
#  alarm_name          = "${local.environment}-availability-dependencies"
#  alarm_description   = "Serve route53 health-checks for route /health-check/dependencies have failed"
#  statistic           = "Minimum"
#  metric_name         = "HealthCheckStatus"
#  comparison_operator = "LessThanThreshold"
#  datapoints_to_alarm = 5
#  threshold           = 1
#  period              = 60
#  evaluation_periods  = 5
#  namespace           = "AWS/Route53"
#  alarm_actions       = [data.aws_sns_topic.alert_us_east.arn]
#  tags                = local.default_tags
#
#  dimensions = {
#    HealthCheckId = aws_route53_health_check.availability_dependencies.id
#  }
#}

# ===== Application Errors =====
resource "aws_cloudwatch_log_metric_filter" "sirius_login_errors" {
  name           = "sirius-login-errors--${local.environment}"
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
  alarm_name          = "${local.environment}-sirius-login-errors"
  alarm_description   = "Serve unable to login to sirius! Check sirius authentication changes"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.sirius_login_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.sirius_login_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]
  tags                = local.default_tags
}

resource "aws_cloudwatch_log_metric_filter" "sirius_unavailable_errors" {
  name           = "sirius-unavailable-errors-${local.environment}"
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
  alarm_description   = "Serve unable to contact sirius! Check sirius availability"
  statistic           = "Sum"
  metric_name         = aws_cloudwatch_log_metric_filter.sirius_unavailable_errors.metric_transformation[0].name
  comparison_operator = "GreaterThanOrEqualToThreshold"
  datapoints_to_alarm = 1
  evaluation_periods  = 1
  threshold           = 1
  period              = 60
  namespace           = aws_cloudwatch_log_metric_filter.sirius_unavailable_errors.metric_transformation[0].namespace
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]
  tags                = local.default_tags
}
