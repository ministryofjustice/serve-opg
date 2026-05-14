# Security Group Rules to allow access to the SSM instance
data "aws_security_group" "ssm_ec2_data_access" {
  name = "data-access-ssm-instance"
}

# Database Connect via Proxy Role
data "aws_iam_role" "data_access" {
  name = "data-access"
}

data "aws_iam_policy_document" "database_readonly_assume" {
  statement {
    sid     = "AllowAssume"
    effect  = "Allow"
    actions = ["sts:AssumeRole"]

    principals {
      type        = "AWS"
      identifiers = [data.aws_iam_role.data_access.arn]
    }
  }
}

resource "aws_iam_role" "database_readonly_access" {
  name               = "readonly-db-iam-${local.environment}"
  assume_role_policy = data.aws_iam_policy_document.database_readonly_assume.json
  tags               = local.default_tags
}

data "aws_iam_policy_document" "database_readonly_connect" {
  statement {
    sid     = "AllowRdsConnect"
    effect  = "Allow"
    actions = ["rds-db:connect"]

    resources = [
      "arn:aws:rds-db:eu-west-1:${data.aws_caller_identity.current.account_id}:dbuser:${aws_rds_cluster.cluster.cluster_resource_id}/readonly-db-iam-${local.environment}"
    ]
  }
}

resource "aws_iam_policy" "database_readonly_connect" {
  name        = "database-readonly-access-${local.environment}"
  description = "Allow database-readonly-access role to connect to RDS via IAM Auth."
  policy      = data.aws_iam_policy_document.database_readonly_connect.json
}

resource "aws_iam_role_policy_attachment" "database_readonly_connect_attach" {
  role       = aws_iam_role.database_readonly_access.name
  policy_arn = aws_iam_policy.database_readonly_connect.arn
}
