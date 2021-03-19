data "aws_availability_zones" "default" {}

data "aws_vpc" "vpc" {
  filter {
    name   = "tag:Name"
    values = ["main*"]
  }
}

data "aws_subnet" "public" {
  count             = 3
  vpc_id            = data.aws_vpc.vpc.id
  availability_zone = data.aws_availability_zones.default.names[count.index]

  filter {
    name   = "tag:Name"
    values = ["public*"]
  }
}

data "aws_subnet" "private" {
  count             = 3
  vpc_id            = data.aws_vpc.vpc.id
  availability_zone = data.aws_availability_zones.default.names[count.index]

  filter {
    name   = "tag:Name"
    values = ["private*"]
  }
}

data "aws_subnet" "persistence" {
  count             = 3
  vpc_id            = data.aws_vpc.vpc.id
  availability_zone = data.aws_availability_zones.default.names[count.index]

  filter {
    name   = "tag:Name"
    values = ["persistence*"]
  }
}