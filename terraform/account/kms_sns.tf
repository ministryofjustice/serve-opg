# KMS Key for the SNS Topic
resource "aws_kms_key" "serve_sns" {
  description             = "KMS key for Serve SNS Slack alerts"
  deletion_window_in_days = 7
  enable_key_rotation     = true

  tags = merge(
    local.default_tags,
    { Name = "serve-sns-slack-${local.account.name}" },
  )
}

resource "aws_kms_alias" "sns_key_alias" {
  name          = "alias/serve-sns"
  target_key_id = aws_kms_key.serve_sns.key_id
}