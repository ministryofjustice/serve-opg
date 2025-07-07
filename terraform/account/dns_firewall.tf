resource "aws_cloudwatch_log_group" "aws_route53_resolver_query_log" {
  count             = local.account.dns_firewall.enabled ? 1 : 0
  name              = "serve-aws-route53-resolver-query-log-config"
  retention_in_days = 180
  kms_key_id        = module.logs_kms.target_key_arn
  tags = {
    "Name" = "serve-aws-route53-resolver-query-log-config"
  }
}

resource "aws_route53_resolver_query_log_config" "egress" {
  count           = local.account.dns_firewall.enabled ? 1 : 0
  name            = "egress"
  destination_arn = aws_cloudwatch_log_group.aws_route53_resolver_query_log[0].arn
}

resource "aws_route53_resolver_query_log_config_association" "egress" {
  count                        = local.account.dns_firewall.enabled ? 1 : 0
  resolver_query_log_config_id = aws_route53_resolver_query_log_config.egress[0].id
  resource_id                  = aws_default_vpc.default.id
}

locals {
  service_id = [
    "logs",
    "ecr",
    "dynamodb",
    "kms",
    "secretsmanager",
    "ecr.api",
    "ssm",
  ]
}

data "aws_service" "services" {
  for_each   = toset(local.service_id)
  region     = data.aws_region.current.name
  service_id = each.value
}

locals {
  aws_service_dns_name = [for service in data.aws_service.services : "${service.dns_name}."]

  default_dns = [
    "sts.amazonaws.com.",
  ]
  # if we put blocks on dev env then these need to be this relaxed as only leading wildcard is valid
  development_dns = [
    "*.private.",
  ]
  production_dns_alert = [
    "api.github.com.",
  ]
  production_dns_allow = [
    "serve.opg.service.justice.gov.uk."
  ]

  production_dns_combined    = concat(local.default_dns, local.production_dns_alert, local.production_dns_allow)
  preproduction_dns_combined = local.default_dns
  development_dns_combined   = concat(local.default_dns, local.development_dns)

  combined_dns = {
    production    = local.production_dns_combined,
    preproduction = local.preproduction_dns_combined
    development   = local.development_dns_combined
  }
}

resource "aws_route53_resolver_firewall_domain_list" "egress_allow" {
  count = local.account.dns_firewall.enabled ? 1 : 0
  name  = "egress_allowed"
  domains = concat(
    local.combined_dns[local.account.name],
    local.aws_service_dns_name,
    local.account.dns_firewall.domains_allowed,
  )
}

resource "aws_route53_resolver_firewall_domain_list" "egress_block" {
  count   = local.account.dns_firewall.enabled ? 1 : 0
  name    = "egress_blocked"
  domains = local.account.dns_firewall.domains_blocked
}

resource "aws_route53_resolver_firewall_rule_group" "egress" {
  count = local.account.dns_firewall.enabled ? 1 : 0
  name  = "egress"
}

resource "aws_route53_resolver_firewall_rule" "egress_allow" {
  count                   = local.account.dns_firewall.enabled ? 1 : 0
  name                    = "egress_allowed"
  action                  = "ALLOW"
  firewall_domain_list_id = aws_route53_resolver_firewall_domain_list.egress_allow[0].id
  firewall_rule_group_id  = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority                = 200
}

resource "aws_route53_resolver_firewall_rule" "egress_block" {
  count  = local.account.dns_firewall.enabled ? 1 : 0
  name   = "egress_blocked"
  action = "ALERT"
  # action                  = "BLOCK"
  # block_response          = "NXDOMAIN"
  firewall_domain_list_id = aws_route53_resolver_firewall_domain_list.egress_block[0].id
  firewall_rule_group_id  = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority                = 300
}

resource "aws_route53_resolver_firewall_rule_group_association" "egress" {
  count                  = local.account.dns_firewall.enabled ? 1 : 0
  name                   = "egress"
  firewall_rule_group_id = aws_route53_resolver_firewall_rule_group.egress[0].id
  priority               = 500
  vpc_id                 = aws_default_vpc.default.id
}


resource "aws_cloudwatch_query_definition" "dns_firewall_statistics" {
  count = local.account.dns_firewall.enabled ? 1 : 0
  name  = "DNS Firewall Queries/DNS Firewall Statistics"

  log_group_names = [aws_cloudwatch_log_group.aws_route53_resolver_query_log[0].name]

  query_string = <<EOF
fields @timestamp, query_name, firewall_rule_action
| sort @timestamp desc
| stats count() as frequency by query_name, firewall_rule_action
EOF
}
