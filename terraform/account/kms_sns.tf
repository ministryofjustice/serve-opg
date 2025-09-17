module "sns_kms" {
  source                  = "./modules/kms_key"
  encrypted_resource      = "SNS"
  kms_key_alias_name      = "serve_sns_encryption_key"
  enable_key_rotation     = true
  deletion_window_in_days = 10
  kms_key_policy          = local.account.name == "development" ? data.aws_iam_policy_document.kms_sns_merged_development.json : data.aws_iam_policy_document.kms_sns_merged.json
}

data "aws_iam_policy_document" "kms_sns_merged" {
  source_policy_documents = [
    data.aws_iam_policy_document.kms_sns_key.json,
    data.aws_iam_policy_document.kms_base_permissions.json
  ]
}

data "aws_iam_policy_document" "kms_sns_merged_development" {
  source_policy_documents = [
    data.aws_iam_policy_document.kms_sns_key.json,
    data.aws_iam_policy_document.kms_base_permissions.json,
    data.aws_iam_policy_document.kms_development_account_operator_admin.json
  ]
}

data "aws_iam_policy_document" "kms_sns_key" {
  statement {
    sid    = "AllowSNSServiceUse"
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["sns.amazonaws.com"]
    }

    actions = [
      "kms:GenerateDataKey*",
      "kms:Decrypt"
    ]

    resources = ["*"]
  }

  statement {
    sid    = "AllowCloudWatchPublish"
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["cloudwatch.amazonaws.com"]
    }

    actions = [
      "kms:GenerateDataKey*",
      "kms:Decrypt"
    ]

    resources = ["*"]
  }
}
