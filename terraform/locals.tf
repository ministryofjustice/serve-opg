module "allow_list" {
  source = "git@github.com:ministryofjustice/opg-terraform-aws-moj-ip-allow-list.git?ref=v3.0.1"
}

locals {
  account_id                             = var.accounts[terraform.workspace]
  dns_prefix                             = local.account.prefix
  capitalized_environment                = "${upper(substr(terraform.workspace, 0, 1))}${substr(terraform.workspace, 1, -1)}"
  service                                = "serve-opg"
  sirius_role                            = var.SIRIUS_ROLE == "serve-assume-role-ci" ? "${var.SIRIUS_ROLE}-${terraform.workspace}" : var.SIRIUS_ROLE
  default_allow_list                     = local.account.ip_whitelist ? module.allow_list.moj_sites : tolist(["0.0.0.0/0"])

  default_tags = {
    business-unit          = "OPG"
    application            = "Serve OPG"
    environment-name       = terraform.workspace
    owner                  = "opgallocations@digital.justice.gov.uk"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.account.is_production
  }
}
