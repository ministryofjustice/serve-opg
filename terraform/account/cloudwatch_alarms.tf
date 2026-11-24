data "aws_nat_gateways" "main" {
  vpc_id = module.network.vpc.id

  filter {
    name   = "state"
    values = ["available"]
  }
}

data "aws_sns_topic" "slack_notification" {
  name = "serve-slack-notifications"
}

resource "aws_cloudwatch_metric_alarm" "nat_gateway_bytes_out" {
  for_each = toset(data.aws_nat_gateways.main.ids)

  alarm_name        = "${local.account.name}-${each.key}-nat-bytes-out"
  alarm_description = "BytesOutToDestination for NAT Gateway ${each.key}"

  actions_enabled = true
  alarm_actions   = [data.aws_sns_topic.slack_notification.arn]
  ok_actions      = []

  namespace   = "AWS/NATGateway"
  metric_name = "BytesOutToDestination"
  statistic   = "Maximum"
  period      = 60

  dimensions = {
    NatGatewayId = each.key
  }

  comparison_operator = "GreaterThanThreshold"
  threshold           = 100000

  evaluation_periods  = 1
  datapoints_to_alarm = 1

  treat_missing_data = "notBreaching"

  tags = local.default_tags
}
