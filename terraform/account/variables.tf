variable "accounts" {
  type = map(
    object({
      account_id = string
    })
  )
}

locals {
  environment             = terraform.workspace
  account                 = var.accounts[local.environment]
  service                 = "serve-opg"
  capitalized_environment = "${upper(substr(terraform.workspace, 0, 1))}${substr(terraform.workspace, 1, -1)}"
  is_production           = local.environment == "production" ? "true" : "false"

  default_tags = {
    business-unit          = "OPG"
    application            = "Serve OPG"
    environment-name       = terraform.workspace
    owner                  = "opgallocations@digital.justice.gov.uk"
    infrastructure-support = "OPG WebOps: opgteam@digital.justice.gov.uk"
    is-production          = local.is_production
  }
}

data "aws_caller_identity" "current" {}
data "aws_region" "current" {}
