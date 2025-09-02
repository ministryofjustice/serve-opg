resource "aws_cloudwatch_event_bus" "serve" {
  name = "serve-bus"
}

resource "aws_cloudwatch_event_rule" "forward_to_sirius" {
  name           = "forward-to-sirius"
  description    = "Forward serve events to sirius supervision bus"
  event_bus_name = aws_cloudwatch_event_bus.serve.name

  event_pattern = jsonencode({
    "source" = ["opg.supervision.serve"]
  })
}

resource "aws_cloudwatch_event_target" "to_sirius_bus" {
  rule           = aws_cloudwatch_event_rule.forward_to_sirius.name
  event_bus_name = aws_cloudwatch_event_bus.serve.name

  arn = "arn:aws:events:eu-west-1:${local.account.sirius_account}:event-bus/ddls-856-supervision"
}

#  arn = "arn:aws:events:eu-west-1:${local.account.sirius_account}:event-bus/${local.account.account_name}-supervision"
