# VPC Endpoints
module "secrets_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "secretsmanager"
  service_short_title = "secrets"
  tags                = local.default_tags
}

module "ecr_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ecr.dkr"
  service_short_title = "ecr"
  tags                = local.default_tags
}

module "ecr_api_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ecr.api"
  service_short_title = "ecr_api"
  tags                = local.default_tags
}

module "logs_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "logs"
  service_short_title = "logs"
  tags                = local.default_tags
}

resource "aws_vpc_endpoint" "s3_endpoint_vpc" {
  service_name      = "com.amazonaws.eu-west-1.s3"
  vpc_id            = module.network.vpc.id
  vpc_endpoint_type = "Gateway"
  route_table_ids   = module.network.application_subnet_route_tables[*].id
  tags              = merge(local.default_tags, { Name = "s3" })
}

resource "aws_vpc_endpoint" "dynamodb_endpoint_vpc" {
  service_name      = "com.amazonaws.eu-west-1.dynamodb"
  vpc_id            = module.network.vpc.id
  vpc_endpoint_type = "Gateway"
  route_table_ids   = module.network.application_subnet_route_tables[*].id
  tags              = merge(local.default_tags, { Name = "dynamodb" })
}
