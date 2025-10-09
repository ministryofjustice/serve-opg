##### Shared Application KMS key for logs #####
data "aws_kms_alias" "logs_encryption" {
  name = "alias/serve_logs_encryption_key"
}

module "disaster_recovery_backup" {
  source                  = "./modules/disaster_recovery"
  count                   = local.account.dr_backup == "true" ? 1 : 0
  account_id              = local.account.account_id
  backup_account_id       = local.backup_account_id
  task_runner_arn         = aws_iam_role.events_task_runner.arn
  execution_role_arn      = aws_iam_role.execution.arn
  cross_account_role_name = local.cross_account_role_name
  image                   = "311462405659.dkr.ecr.eu-west-1.amazonaws.com/serve-opg/backup:${var.APP_VERSION}"
  aws_ecs_cluster_arn     = aws_ecs_cluster.serve_opg.arn
  aws_subnet_ids          = data.aws_subnet.private[*].id
  aws_vpc_id              = data.aws_vpc.vpc.id
  logs_kms_key_arn        = data.aws_kms_alias.logs_encryption.arn
  log_retention           = 30
  task_role_assume_policy = data.aws_iam_policy_document.task_role_assume_policy
  environment             = local.environment
  default_tags            = local.default_tags
}
