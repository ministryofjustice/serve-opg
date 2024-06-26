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
