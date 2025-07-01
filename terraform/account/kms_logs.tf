module "logs_kms" {
  source                  = "./modules/kms_key"
  encrypted_resource      = "Logs"
  kms_key_alias_name      = "serve_logs_encryption_key"
  enable_key_rotation     = true
  deletion_window_in_days = 10
  kms_key_policy          = local.account.name == "development" ? data.aws_iam_policy_document.kms_logs_merged_for_development.json : data.aws_iam_policy_document.kms_logs_merged.json
}

# ===== Policy for KMS Logs =====

data "aws_iam_policy_document" "kms_logs_merged_for_development" {
  source_policy_documents = [
    data.aws_iam_policy_document.kms_logs.json,
    data.aws_iam_policy_document.kms_base_permissions.json,
    data.aws_iam_policy_document.kms_development_account_operator_admin.json
  ]
}

data "aws_iam_policy_document" "kms_logs_merged" {
  source_policy_documents = [
    data.aws_iam_policy_document.kms_logs.json,
    data.aws_iam_policy_document.kms_base_permissions.json
  ]
}

data "aws_iam_policy_document" "kms_logs" {
  statement {
    sid       = "Allow Key to be used for Encryption"
    effect    = "Allow"
    resources = ["*"]
    actions = [
      "kms:Encrypt",
      "kms:Decrypt",
      "kms:ReEncrypt*",
      "kms:GenerateDataKey*",
      "kms:DescribeKey",
    ]

    principals {
      type = "Service"
      identifiers = [
        "logs.${data.aws_region.current.name}.amazonaws.com",
        "events.amazonaws.com"
      ]
    }
  }
}
