###############################################################################
# Secrets Manager
###############################################################################

module "secrets_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "secretsmanager"
  service_short_title = "secrets"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.secrets_endpoint.json : ""
  tags                = local.default_tags
}

data "aws_iam_policy_document" "secrets_endpoint" {
  statement {
    sid    = "AllowSecrets"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "secretsmanager:GetSecretValue",
      "secretsmanager:DescribeSecret"
    ]

    resources = [
      "arn:aws:secretsmanager:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:*"
    ]
  }
}

###############################################################################
# Elastic Container Registry (ECR)
###############################################################################

module "ecr_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ecr.dkr"
  service_short_title = "ecr"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.ecr_endpoint.json : ""
  tags                = local.default_tags
}

module "ecr_api_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ecr.api"
  service_short_title = "ecr_api"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.ecr_endpoint.json : ""
  tags                = local.default_tags
}

data "aws_iam_policy_document" "ecr_endpoint" {
  statement {
    sid    = "AllowROAccessToECR"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "ecr:Get*",
      "ecr:Describe*",
      "ecr:BatchGetImage"
    ]

    resources = [
      "arn:aws:ecr:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:*",
      "arn:aws:ecr:${data.aws_region.current.name}:${local.management_account_id}:*"
    ]
  }

  statement {
    sid    = "AllowGetAuthToken"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "ecr:GetAuthorizationToken"
    ]

    resources = ["*"]
  }
}

###############################################################################
# CloudWatch / Logs / RUM
###############################################################################

module "logs_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "logs"
  service_short_title = "logs"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.logs_endpoint.json : ""
  tags                = local.default_tags
}

data "aws_iam_policy_document" "logs_endpoint" {
  statement {
    sid    = "AllowMonitoringServices"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "rum:ListAppMonitors",
      "logs:Get*",
      "logs:List*",
      "logs:Describe*",
      "logs:Create*",
      "logs:Put*",
      "monitoring:Get*",
      "monitoring:List*",
      "monitoring:Describe*"
    ]

    resources = [
      "arn:aws:logs:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:*",
      "arn:aws:rum:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:*",
      "arn:aws:monitoring:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:*"
    ]
  }
}

###############################################################################
# Systems Manager
###############################################################################

module "ssm_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ssm"
  service_short_title = "ssm"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.ssm_endpoint.json : ""
  tags                = local.default_tags
}

data "aws_iam_policy_document" "ssm_endpoint" {
  statement {
    sid    = "AllowSSMFromThisAccount"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "ssm:*"
    ]

    resources = ["*"]
  }
}

module "ssmmessages_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ssmmessages"
  service_short_title = "ssmmessages"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.ssmmessages_endpoint.json : ""
  tags                = local.default_tags
}

data "aws_iam_policy_document" "ssmmessages_endpoint" {
  statement {
    sid    = "AllowSSMMessagesFromThisAccount"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "ssmmessages:*"
    ]

    resources = ["*"]
  }
}

module "ec2messages_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "ec2messages"
  service_short_title = "ec2messages"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.ec2messages_endpoint.json : ""
  tags                = local.default_tags
}

data "aws_iam_policy_document" "ec2messages_endpoint" {
  statement {
    sid    = "AllowEC2MessagesFromThisAccount"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "ec2messages:*"
    ]

    resources = ["*"]
  }
}

###############################################################################
# Security Token Service (STS)
###############################################################################

module "sts_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "sts"
  service_short_title = "sts"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.sts_endpoint.json : ""
  tags                = local.default_tags
}

data "aws_iam_policy_document" "sts_endpoint" {
  statement {
    sid    = "AllowSTSFromThisAccount"
    effect = "Allow"

    principals {
      type        = "AWS"
      identifiers = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"]
    }

    actions = [
      "sts:*"
    ]

    resources = ["*"]
  }
}

###############################################################################
# RDS API
###############################################################################

module "rds_endpoint_vpc" {
  source              = "./modules/vpc_endpoint"
  subnet_ids          = module.network.application_subnets[*].id
  vpc                 = module.network.vpc
  region              = data.aws_region.current.name
  service             = "rds"
  service_short_title = "rds"
  policy              = local.account.name == "development" ? data.aws_iam_policy_document.rds_api_endpoint.json : ""
  tags                = local.default_tags
}

data "aws_iam_policy_document" "rds_api_endpoint" {
  statement {
    sid    = "AllowApprovedRDSOperations"
    effect = "Allow"

    principals {
      type        = "*"
      identifiers = ["*"]
    }

    actions = [
      "rds:Describe*",
      "rds:*Snapshot*"
    ]

    resources = ["*"]
  }
}

###############################################################################
# DynamoDB Gateway Endpoint
###############################################################################

resource "aws_vpc_endpoint" "dynamodb_endpoint_vpc" {
  service_name      = "com.amazonaws.eu-west-1.dynamodb"
  vpc_id            = module.network.vpc.id
  vpc_endpoint_type = "Gateway"
  route_table_ids   = module.network.application_subnet_route_tables[*].id
  policy            = local.account.name == "development" ? data.aws_iam_policy_document.dynamodb_endpoint.json : ""
  tags              = merge(local.default_tags, { Name = "dynamodb" })
}

data "aws_iam_policy_document" "dynamodb_endpoint" {
  statement {
    sid    = "AllowDynamoDBFromThisAccount"
    effect = "Allow"

    principals {
      type        = "*"
      identifiers = ["*"]
    }

    condition {
      test     = "ArnLike"
      variable = "aws:PrincipalArn"
      values   = ["arn:aws:iam::${data.aws_caller_identity.current.account_id}:role/frontend-*"]
    }
    
    actions = [
      "dynamodb:GetItem",
      "dynamodb:PutItem",
      "dynamodb:UpdateItem",
      "dynamodb:DeleteItem",
      "dynamodb:Query",
      "dynamodb:Scan",
      "dynamodb:BatchGetItem",
      "dynamodb:BatchWriteItem",
      "dynamodb:DescribeTable"
    ]

    resources = [
      "arn:aws:dynamodb:${data.aws_region.current.name}:${data.aws_caller_identity.current.account_id}:table/*"
    ]
  }
}

###############################################################################
# S3 Gateway Endpoint
###############################################################################


resource "aws_vpc_endpoint" "s3_endpoint_vpc" {
  service_name      = "com.amazonaws.eu-west-1.s3"
  vpc_id            = module.network.vpc.id
  vpc_endpoint_type = "Gateway"
  route_table_ids   = module.network.application_subnet_route_tables[*].id
  policy            = local.account.name == "development" ? data.aws_iam_policy_document.s3_endpoint.json : ""
  tags              = merge(local.default_tags, { Name = "s3" })
}

data "aws_iam_policy_document" "s3_endpoint" {
  statement {
    sid    = "AllowAmazonRepo"
    effect = "Allow"
    principals {
      type        = "*"
      identifiers = ["*"]
    }
    actions = [
      "s3:GetObject",
      "s3:ListBucket"
    ]
    resources = [
      "arn:aws:s3:::al2023-repos-eu-west-1-de612dc2",
      "arn:aws:s3:::al2023-repos-eu-west-1-de612dc2/*"
    ]
  }

  statement {
    sid    = "AllowApprovedBuckets"
    effect = "Allow"

    principals {
      type        = "*"
      identifiers = ["*"]
    }

    actions = [
      "s3:Get*",
      "s3:List*",
      "s3:Put*",
      "s3:HeadBucket",
      "s3:Create*",
      "s3:AbortMultipartUpload",
      "s3:Upload*",
    ]

    resources = [
      # Service bucket
      "arn:aws:s3:::*serve-opg.opg.digital",
      "arn:aws:s3:::*serve-opg.opg.digital/*",

      # Orchestration bucket
      "arn:aws:s3:::*.orchestration.serve.opg.digital",
      "arn:aws:s3:::*.orchestration.serve.opg.digital/*",

      # CloudTrail
      "arn:aws:s3:::cloudtrail.${local.account.name}.serve.opg.service.justice.gov.uk",
      "arn:aws:s3:::cloudtrail.${local.account.name}.serve.opg.service.justice.gov.uk/*",

      # Config
      "arn:aws:s3:::config-eu-west-1-${local.account.name}-serve-opg-opg",
      "arn:aws:s3:::config-eu-west-1-${local.account.name}-serve-opg-opg/*",
      "arn:aws:s3:::config-eu-west-2-${local.account.name}-serve-opg-opg",
      "arn:aws:s3:::config-eu-west-2-${local.account.name}-serve-opg-opg/*",

      # Access logs
      "arn:aws:s3:::s3-access-logs-opg-serve-opg-${local.account.name}-eu-west-1",
      "arn:aws:s3:::s3-access-logs-opg-serve-opg-${local.account.name}-eu-west-1/*",
      "arn:aws:s3:::s3-access-logs-opg-serve-opg-${local.account.name}-eu-west-2",
      "arn:aws:s3:::s3-access-logs-opg-serve-opg-${local.account.name}-eu-west-2/*",

      # Shared logging bucket
      "arn:aws:s3:::logs.serve-opg.opg.digital",
      "arn:aws:s3:::logs.serve-opg.opg.digital/*",

      # Lambda layer buckets
      "arn:aws:s3:::prod-eu-west-1-starport-layer-bucket",
      "arn:aws:s3:::prod-eu-west-1-starport-layer-bucket/*",
      "arn:aws:s3:::prod-eu-west-2-starport-layer-bucket",
      "arn:aws:s3:::prod-eu-west-2-starport-layer-bucket/*",
    ]
  }
}
