module "secrets_kms" {
  source                  = "./modules/kms_key"
  encrypted_resource      = "secrets"
  kms_key_alias_name      = "serve_secrets_encryption_key"
  enable_key_rotation     = true
  deletion_window_in_days = 10
  kms_key_policy          = local.account.name == "development" ? data.aws_iam_policy_document.kms_secrets_merged_development.json : data.aws_iam_policy_document.kms_secrets_merged.json
}

data "aws_iam_policy_document" "kms_secrets_merged" {
  source_policy_documents = [
    data.aws_iam_policy_document.kms_secrets_key.json,
    data.aws_iam_policy_document.kms_base_permissions.json
  ]
}

data "aws_iam_policy_document" "kms_secrets_merged_development" {
  source_policy_documents = [
    data.aws_iam_policy_document.kms_secrets_key.json,
    data.aws_iam_policy_document.kms_base_permissions.json,
    data.aws_iam_policy_document.kms_development_account_operator_admin.json
  ]
}


data "aws_iam_policy_document" "kms_secrets_key" {
  statement {
    sid       = "Allow Key to be used for Encryption by Secret Manager"
    effect    = "Allow"
    resources = ["*"]
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey"
    ]

    principals {
      type = "Service"
      identifiers = [
        "secretsmanager.amazonaws.com"
      ]
    }
  }
}
