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
