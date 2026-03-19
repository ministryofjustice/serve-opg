resource "aws_default_vpc" "default" {
  tags = merge(
    local.default_tags,
    { Name = "main" },
  )
}

data "aws_availability_zones" "default" {}

data "aws_internet_gateway" "default" {
  filter {
    name   = "attachment.vpc-id"
    values = [aws_default_vpc.default.id]
  }
}

resource "aws_default_vpc_dhcp_options" "default" {
  tags = local.default_tags
}

resource "aws_iam_service_linked_role" "ecs" {
  aws_service_name = "ecs.amazonaws.com"
}

resource "aws_db_subnet_group" "database" {
  name       = "data-subnet-group-${local.account.name}"
  subnet_ids = module.network.data_subnets[*].id
  tags = merge(
    local.default_tags,
    { Name = "data-subnet-group-${local.account.name}" },
  )
}
