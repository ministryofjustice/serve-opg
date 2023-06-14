resource "aws_cloud9_environment_ec2" "shared" {
  instance_type               = "t1.micro"
  name                        = "${terraform.workspace}-cloud9-env"
  automatic_stop_time_minutes = 20
  description                 = "Shared Cloud9 instance to be used by all devs"
  subnet_id                   = aws_default_subnet.public[0].id
  owner_arn                   = "arn:aws:iam::${var.accounts[terraform.workspace]}:assumed-role/operator/tom.gulliver"
  tags                        = local.default_tags
}

resource "aws_cloud9_environment_membership" "shared" {
  for_each = toset(local.cloud9_users)

  environment_id = aws_cloud9_environment_ec2.shared.id
  permissions    = "read-write"
  user_arn       = "arn:aws:iam::${var.accounts[terraform.workspace]}:assumed-role/operator/${each.value}"
}
