resource "local_file" "cluster_config" {
  content  = jsonencode(local.cluster_config)
  filename = "${path.module}/cluster_config.json"
}

locals {
  cluster_config = {
    account_id                        = local.account_id
    load_balancer_security_group_name = aws_security_group.loadbalancer.name
  }
}
