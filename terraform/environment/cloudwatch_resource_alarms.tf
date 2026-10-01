# ECS resource alarms

resource "aws_cloudwatch_metric_alarm" "ecs_frontend_high_cpu" {
  alarm_name          = "${local.environment}-frontend-high-cpu"
  alarm_description   = "Serve frontend ECS CPU usage has remained at or above 85% for 45 minutes"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  metric_name         = "CPUUtilization"
  namespace           = "AWS/ECS"
  statistic           = "Average"
  threshold           = 85
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"
  actions_enabled     = local.account.resource_alarms_active
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]
  ok_actions          = [data.aws_sns_topic.slack_notification.arn]
  tags                = local.default_tags

  dimensions = {
    ClusterName = aws_ecs_cluster.serve_opg.name
    ServiceName = aws_ecs_service.frontend.name
  }
}

resource "aws_cloudwatch_metric_alarm" "ecs_frontend_high_memory" {
  alarm_name          = "${local.environment}-frontend-high-memory"
  alarm_description   = "Serve frontend ECS memory usage has remained at or above 85% for 45 minutes"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  metric_name         = "MemoryUtilization"
  namespace           = "AWS/ECS"
  statistic           = "Average"
  threshold           = 85
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"
  actions_enabled     = local.account.resource_alarms_active
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]
  ok_actions          = [data.aws_sns_topic.slack_notification.arn]
  tags                = local.default_tags

  dimensions = {
    ClusterName = aws_ecs_cluster.serve_opg.name
    ServiceName = aws_ecs_service.frontend.name
  }
}

# Aurora resource alarms

resource "aws_cloudwatch_metric_alarm" "aurora_high_cpu" {
  count = local.account.rds_instance_count

  alarm_name          = "${aws_rds_cluster_instance.instances[count.index].identifier}-high-cpu"
  alarm_description   = "Serve Aurora CPU usage has remained at or above 85% for 45 minutes"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  metric_name         = "CPUUtilization"
  namespace           = "AWS/RDS"
  statistic           = "Average"
  threshold           = 85
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"
  actions_enabled     = local.account.resource_alarms_active
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]
  ok_actions          = [data.aws_sns_topic.slack_notification.arn]
  tags                = local.default_tags

  dimensions = {
    DBInstanceIdentifier = aws_rds_cluster_instance.instances[count.index].identifier
  }
}

resource "aws_cloudwatch_metric_alarm" "aurora_high_acu" {
  count = local.account.rds_instance_count

  alarm_name          = "${aws_rds_cluster_instance.instances[count.index].identifier}-high-acu"
  alarm_description   = "Serve Aurora ACU usage has remained at or above 85% for 45 minutes"
  comparison_operator = "GreaterThanOrEqualToThreshold"
  metric_name         = "ACUUtilization"
  namespace           = "AWS/RDS"
  statistic           = "Average"
  threshold           = 85
  period              = 300
  evaluation_periods  = 9
  datapoints_to_alarm = 9
  treat_missing_data  = "notBreaching"
  actions_enabled     = local.account.resource_alarms_active
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]
  ok_actions          = [data.aws_sns_topic.slack_notification.arn]
  tags                = local.default_tags

  dimensions = {
    DBInstanceIdentifier = aws_rds_cluster_instance.instances[count.index].identifier
  }
}
