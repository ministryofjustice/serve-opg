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
      dc_gtm              = string
      fixtures_enabled    = string
      sirius_key_alias    = string
      sirius_account      = string
      waf_enabled         = string
      ip_whitelist        = string
      deletion_protection = string
      postgres_version    = string
      cloud9_env_id       = string
    })
  )
}

locals {
  environment        = terraform.workspace
  account            = contains(keys(var.accounts), local.environment) ? var.accounts[local.environment] : var.accounts["default"]
  management         = "311462405659"
  dns_prefix         = local.environment == "production" ? "serve" : "${local.environment}.serve"
  sirius_role        = var.SIRIUS_ROLE == "serve-assume-role-ci" ? "${var.SIRIUS_ROLE}-${local.account.account_name}" : var.SIRIUS_ROLE
  default_allow_list = local.account.ip_whitelist ? module.allow_list.moj_sites : tolist(["0.0.0.0/0"])

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
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git?ref=v3.0.1"
}
