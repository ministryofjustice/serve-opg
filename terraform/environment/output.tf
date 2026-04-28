output "Role" {
  value = "arn:aws:iam::${local.account.account_id}:role/${var.DEFAULT_ROLE}"
}


output "Services" {
  value = {
    Cluster = aws_ecs_cluster.serve_opg.name
    Services = [
      aws_ecs_service.frontend.name
    ]
  }
}

output "Tasks" {
  value = {
    backup  = module.backup.render
    restore = module.restore.render
  }
}
