# ===== Public Network =====
resource "aws_default_subnet" "public" {
  count             = 3
  availability_zone = data.aws_availability_zones.default.names[count.index]
  tags = merge(
    local.default_tags,
    { Name = "public" },
  )
}

resource "aws_default_route_table" "default" {
  default_route_table_id = aws_default_vpc.default.default_route_table_id
  tags = merge(
    local.default_tags,
    { Name = "public" },
  )
}

resource "aws_route" "default" {
  route_table_id         = aws_default_route_table.default.id
  destination_cidr_block = "0.0.0.0/0"
  gateway_id             = data.aws_internet_gateway.default.id
}

resource "aws_eip" "nat" {
  count = 3
  tags  = local.default_tags
}

resource "aws_nat_gateway" "nat" {
  count         = 3
  allocation_id = element(aws_eip.nat[*].id, count.index)
  subnet_id     = element(aws_default_subnet.public[*].id, count.index)
  tags          = local.default_tags
}

resource "aws_default_security_group" "default" {
  vpc_id = aws_default_vpc.default.id
  tags   = local.default_tags
}

output "nat_ips" {
  value = aws_nat_gateway.nat[*].public_ip
}

# ===== Private Network =====
resource "aws_subnet" "private" {
  count             = 3
  cidr_block        = cidrsubnet(aws_default_vpc.default.cidr_block, 4, count.index + 3)
  availability_zone = data.aws_availability_zones.default.names[count.index]
  vpc_id            = aws_default_vpc.default.id
  tags = merge(
    local.default_tags,
    { Name = "private" },
  )
}

resource "aws_route_table" "private" {
  count  = 3
  vpc_id = aws_default_vpc.default.id
  tags = merge(
    local.default_tags,
    { Name = "private" },
  )
}

resource "aws_route_table_association" "private" {
  count          = 3
  route_table_id = element(aws_route_table.private[*].id, count.index)
  subnet_id      = element(aws_subnet.private[*].id, count.index)
}

resource "aws_route" "private" {
  count                  = 3
  route_table_id         = element(aws_route_table.private[*].id, count.index)
  destination_cidr_block = "0.0.0.0/0"
  nat_gateway_id         = element(aws_nat_gateway.nat[*].id, count.index)
}

# ===== Persistence Network =====
resource "aws_subnet" "data_persistence" {
  count             = 3
  cidr_block        = cidrsubnet(aws_default_vpc.default.cidr_block, 4, count.index + 6)
  availability_zone = data.aws_availability_zones.default.names[count.index]
  vpc_id            = aws_default_vpc.default.id
  tags = merge(
    local.default_tags,
    { Name = "persistence" },
  )
}

resource "aws_route_table" "data_persistence" {
  count  = 3
  vpc_id = aws_default_vpc.default.id

  tags = merge(
    local.default_tags,
    { Name = "persistence" },
  )
}

resource "aws_route_table_association" "data_persistence" {
  count          = 3
  subnet_id      = aws_subnet.data_persistence[count.index].id
  route_table_id = aws_route_table.data_persistence[count.index].id
}



# ==== New Network ====

locals {
  firewall_config = lookup(local.account_level_configurations, terraform.workspace, local.account_level_configurations["production"])
  account_level_configurations = {
    development = {
      network_firewall_enabled      = false
      none_matching_traffic_action  = "alert"
      shared_firewall_configuration = null
      #      shared_firewall_configuration = {
      #        account_id   = "705467933182"
      #        account_name = "development"
      #      }
    }
    preproduction = {
      network_firewall_enabled      = false
      none_matching_traffic_action  = "alert"
      shared_firewall_configuration = null
      #      shared_firewall_configuration = {
      #        account_id   = "540070264006"
      #        account_name = "production"
      #      }
    }
    production = {
      network_firewall_enabled      = false
      none_matching_traffic_action  = "alert"
      shared_firewall_configuration = null
      #      shared_firewall_configuration = {
      #        account_id   = "933639921819"
      #        account_name = "production"
      #      }
    }
  }
  allowed_domains = []

  allowed_prefixed_domains = []
}

module "network" {
  source                                                  = "git@github.com:ministryofjustice/opg-terraform-aws-firewalled-network.git?ref=v1.1.0"
  cidr                                                    = data.aws_region.current.name == "eu-west-1" ? local.account.network.cidr_eu_west_1 : local.account.network.cidr_eu_west_2
  default_security_group_ingress                          = []
  default_security_group_egress                           = []
  dhcp_options_domain_name                                = "${local.account.name}.internal"
  enable_dns_hostnames                                    = true
  flow_log_cloudwatch_log_group_kms_key_id                = module.logs_kms.target_key_arn
  flow_log_cloudwatch_log_group_retention_in_days         = 400
  network_firewall_enabled                                = local.firewall_config.network_firewall_enabled
  shared_firewall_configuration                           = local.firewall_config.shared_firewall_configuration
  network_firewall_cloudwatch_log_group_kms_key_id        = module.logs_kms.target_key_arn
  network_firewall_cloudwatch_log_group_retention_in_days = 400
  aws_networkfirewall_firewall_policy                     = local.firewall_config.shared_firewall_configuration != null ? null : aws_networkfirewall_firewall_policy.main
}

resource "aws_networkfirewall_firewall_policy" "main" {
  name = "main"

  firewall_policy {
    stateless_default_actions          = ["aws:forward_to_sfe"]
    stateless_fragment_default_actions = ["aws:forward_to_sfe"]

    stateful_engine_options {
      rule_order              = "DEFAULT_ACTION_ORDER"
      stream_exception_policy = "DROP"
    }
    stateful_rule_group_reference {
      resource_arn = aws_networkfirewall_rule_group.rule_file.arn
    }
  }
}

resource "aws_networkfirewall_rule_group" "rule_file" {
  capacity = 100
  name     = "main-${replace(filebase64sha256("${path.module}/network_firewall_rules.rules.tpl"), "/[^[:alnum:]]/", "")}"
  type     = "STATEFUL"
  rules = templatefile("${path.module}/network_firewall_rules.rules.tpl", {
    action                   = local.firewall_config.none_matching_traffic_action
    allowed_domains          = local.allowed_domains
    allowed_prefixed_domains = local.allowed_prefixed_domains
    }
  )
  lifecycle {
    create_before_destroy = true
  }
}
