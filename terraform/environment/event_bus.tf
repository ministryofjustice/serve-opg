locals {
  sirius_event_bus_arn = "arn:aws:events:eu-west-1:${local.account.sirius_account}:event-bus/ddls-856-supervision"
}

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

# Cross account target
resource "aws_cloudwatch_event_target" "to_sirius_bus" {
  rule           = aws_cloudwatch_event_rule.forward_to_sirius.name
  event_bus_name = aws_cloudwatch_event_bus.serve.name

  arn      = local.sirius_event_bus_arn
  role_arn = aws_iam_role.cross_account_put.arn
}

# Cross account role
resource "aws_iam_role" "cross_account_put" {
  name               = "cross-account-put-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.assume_role.json
}

resource "aws_iam_role_policy" "cross_account_put" {
  name   = "cross-account-put-${local.environment}"
  policy = data.aws_iam_policy_document.cross_account_put_access.json
  role   = aws_iam_role.cross_account_put.id
}

data "aws_iam_policy_document" "assume_role" {
  statement {
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["events.amazonaws.com"]
    }

    actions = ["sts:AssumeRole"]
  }
}

data "aws_iam_policy_document" "cross_account_put_access" {
  statement {
    sid    = "CrossAccountPutAccess"
    effect = "Allow"
    actions = [
      "events:PutEvents",
    ]
    resources = [
      local.sirius_event_bus_arn
    ]
  }
}
