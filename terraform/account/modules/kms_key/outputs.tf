output "kms_key_alias_name" {
  value = aws_kms_alias.main_eu_west_1.name
}

output "target_key_arn" {
  value = aws_kms_alias.main_eu_west_1.target_key_arn
}

output "target_key_id" {
  value = aws_kms_alias.main_eu_west_1.target_key_id
}
