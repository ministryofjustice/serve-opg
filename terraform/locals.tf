variable "accounts" {
  type = map(string)
}

variable "prefixes" {
  type = map(string)
}

variable "behat_controller" {
  type = map(string)
}

variable "sirius_api" {
  type = map(string)
}

variable "sirius_bucket" {
  type = map(string)
}

variable "is_production" {
  type = map(string)
}

variable "dc_gtm" {
  type = map(string)
}

variable "bucket_names" {
  type = map(string)
}

variable "fixtures_enabled" {
  type = map(string)
}

variable "sirius_key_alias" {
  type = map(string)
}

variable "sirius_account" {
  type = map(string)
}

variable "waf_enabled" {
  type = map(string)
}

variable "ip_whitelist" {
  type = map(string)
}

variable "deletion_protection" {
  type = map(string)
}

variable "postgres_version" {
  type = map(string)
}

module "allow_list" {
  source = "git@github.com:ministryofjustice/terraform-aws-moj-ip-whitelist.git"
}

locals {
  account_id                             = var.accounts[terraform.workspace]
  dns_prefix                             = var.prefixes[terraform.workspace]
  bucket_name                            = var.bucket_names[terraform.workspace]
  behat_controller_enabled               = var.behat_controller[terraform.workspace]
  sirius_api_url                         = var.sirius_api[terraform.workspace]
  sirius_bucket_name                     = var.sirius_bucket[terraform.workspace]
  dc_gtm                                 = var.dc_gtm[terraform.workspace]
  capitalized_environment                = "${upper(substr(terraform.workspace, 0, 1))}${substr(terraform.workspace, 1, -1)}"
  sirius_key_alias                       = var.sirius_key_alias[terraform.workspace]
  sirius_account                         = var.sirius_account[terraform.workspace]
  service                                = "serve-opg"
  sirius_role                            = var.SIRIUS_ROLE == "serve-assume-role-ci" ? "${var.SIRIUS_ROLE}-${terraform.workspace}" : var.SIRIUS_ROLE
  associate_alb_with_waf_web_acl_enabled = var.waf_enabled[terraform.workspace]
  default_allow_list                     = var.ip_whitelist[terraform.workspace] ? module.allow_list.moj_sites : tolist(["0.0.0.0/0"])
  postgres_engine_version                = var.postgres_version[terraform.workspace]
  rds_deletion_protection                = var.deletion_protection[terraform.workspace]
  fixtures_enabled                       = var.fixtures_enabled[terraform.workspace]

  default_tags = {
    business-unit          = "OPG"
    application            = "Serve OPG"
    environment-name       = terraform.workspace
    owner                  = "opgallocations@digital.justice.gov.uk"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = var.is_production[terraform.workspace]
  }
}

