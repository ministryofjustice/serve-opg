resource "aws_kms_key" "main" {
  description             = "serve ${var.encrypted_resource} encryption key"
  deletion_window_in_days = var.deletion_window_in_days
  enable_key_rotation     = var.enable_key_rotation
  policy                  = var.kms_key_policy
}

resource "aws_kms_alias" "main_eu_west_1" {
  name          = "alias/${var.kms_key_alias_name}"
  target_key_id = aws_kms_key.main.key_id
}
