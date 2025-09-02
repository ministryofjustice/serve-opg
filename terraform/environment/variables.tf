variable "APP_VERSION" {
  type        = string
  description = "Version of the app to deploy"
}

variable "accounts" {
  type = map(
    object({
      account_id          = string
      account_name        = string
      behat_controller    = number
      sirius_api          = string
      sirius_bucket       = string
      is_production       = string
      fixtures_enabled    = string
      sirius_key_alias    = string
      sirius_account      = string
      sirius_account_name = string
      waf_enabled         = string
      ip_whitelist        = string
      deletion_protection = string
      postgres_version    = string
      rds_instance_count  = number
      cloud9_env_id       = string
      use_event_bus       = string
    })
  )
}

locals {
  environment          = terraform.workspace
  account              = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  management           = "311462405659"
  dns_prefix           = local.environment == "production" ? "serve" : "${local.environment}.serve"
  default_allow_list   = local.account.ip_whitelist ? concat(module.allow_list.palo_alto_prisma_access, module.allow_list.moj_sites) : tolist(["0.0.0.0/0"])
  sirius_key_alias_arn = "arn:aws:kms:eu-west-1:${local.account.sirius_account}:alias/${local.account.sirius_key_alias}"

  default_tags = {
    business-unit          = "OPG"
    application            = "Serve OPG"
    environment-name       = local.environment
    owner                  = "opgallocations@digital.justice.gov.uk"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.account.is_production
  }
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git?ref=v3.0.3"
}
