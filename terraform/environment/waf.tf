data "aws_wafv2_web_acl" "main" {
  name  = "${local.account.account_name}-web-acl"
  scope = "REGIONAL"
}

resource "aws_wafv2_web_acl_association" "loadbalancer" {
  count        = local.account.waf_enabled ? 1 : 0
  resource_arn = aws_lb.frontend.arn
  web_acl_arn  = data.aws_wafv2_web_acl.main.arn
}

data "aws_caller_identity" "current" {}
data "aws_region" "current" {}
