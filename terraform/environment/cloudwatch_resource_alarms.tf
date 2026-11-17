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

locals {
    aurora_unusual_read_throughput_minimum  = 3000000
    aurora_unusual_network_transmit_minimum = 1000000
    aurora_unusual_read_iops_minimum        = 500
    aurora_anomaly_detection_band_width     = 12
}

resource "aws_cloudwatch_metric_alarm" "aurora_unusual_throughput" {
  count = local.account.rds_instance_count

  alarm_name          = "${aws_rds_cluster_instance.instances[count.index].identifier}-unusual-throughput"
  alarm_description   = "Serve Aurora has experienced unusually high read or network transmit throughput for at least 10 minutes."
  comparison_operator = "GreaterThanOrEqualToThreshold"
  evaluation_periods  = 2
  datapoints_to_alarm = 2
  threshold           = 1
  treat_missing_data  = "notBreaching"
  actions_enabled     = local.account.resource_alarms_active
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]
  ok_actions          = [data.aws_sns_topic.slack_notification.arn]
  tags                = local.default_tags

  metric_query {
    id          = "unusual_throughput"
    expression  = "IF((read_throughput > normal_read_throughput_limit AND read_throughput > ${local.aurora_unusual_read_throughput_minimum}) OR (network_transmit > normal_network_transmit_limit AND network_transmit > ${local.aurora_unusual_network_transmit_minimum}), 1, 0)"
    label       = "Unusual database throughput"
    return_data = true
  }

  metric_query {
    id          = "normal_read_throughput_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(read_throughput, ${local.aurora_anomaly_detection_band_width}))"
    label       = "Normal read throughput limit"
    return_data = false
  }

  metric_query {
    id          = "normal_network_transmit_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(network_transmit, ${local.aurora_anomaly_detection_band_width}))"
    label       = "Normal network transmit limit"
    return_data = false
  }

  metric_query {
    id          = "read_throughput"
    return_data = false

    metric {
      namespace   = "AWS/RDS"
      metric_name = "ReadThroughput"
      period      = 300
      stat        = "Average"

      dimensions = {
        DBInstanceIdentifier = aws_rds_cluster_instance.instances[count.index].identifier
      }
    }
  }

  metric_query {
    id          = "network_transmit"
    return_data = false

    metric {
      namespace   = "AWS/RDS"
      metric_name = "NetworkTransmitThroughput"
      period      = 300
      stat        = "Average"

      dimensions = {
        DBInstanceIdentifier = aws_rds_cluster_instance.instances[count.index].identifier
      }
    }
  }
}

resource "aws_cloudwatch_metric_alarm" "aurora_unusual_read_iops" {
  count = local.account.rds_instance_count

  alarm_name          = "${aws_rds_cluster_instance.instances[count.index].identifier}-unusual-read-iops"
  alarm_description   = "Serve Aurora has experienced unusually high read IOPS for at least 10 minutes."
  comparison_operator = "GreaterThanOrEqualToThreshold"
  evaluation_periods  = 2
  datapoints_to_alarm = 2
  threshold           = 1
  treat_missing_data  = "notBreaching"
  actions_enabled     = local.account.resource_alarms_active
  alarm_actions       = [data.aws_sns_topic.slack_notification.arn]
  ok_actions          = [data.aws_sns_topic.slack_notification.arn]
  tags                = local.default_tags

  metric_query {
    id          = "unusual_read_iops"
    expression  = "IF(read_iops > normal_read_iops_limit AND read_iops > ${local.aurora_unusual_read_iops_minimum}, 1, 0)"
    label       = "Unusual read IOPS"
    return_data = true
  }

  metric_query {
    id          = "normal_read_iops_limit"
    expression  = "LAST(ANOMALY_DETECTION_BAND(read_iops, ${local.aurora_anomaly_detection_band_width}))"
    label       = "Normal read IOPS limit"
    return_data = false
  }

  metric_query {
    id          = "read_iops"
    return_data = false

    metric {
      namespace   = "AWS/RDS"
      metric_name = "ReadIOPS"
      period      = 300
      stat        = "Average"

      dimensions = {
        DBInstanceIdentifier = aws_rds_cluster_instance.instances[count.index].identifier
      }
    }
  }
}
