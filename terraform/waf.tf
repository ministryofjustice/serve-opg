data "aws_wafv2_web_acl" "main" {
  name  = "${terraform.workspace}-web-acl"
  scope = "REGIONAL"
}

resource "aws_wafv2_web_acl_association" "loadbalancer" {
  count        = local.account.waf_enabled ? 1 : 0
  resource_arn = aws_lb.loadbalancer.arn
  web_acl_arn  = data.aws_wafv2_web_acl.main.arn
}
