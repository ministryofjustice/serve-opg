variable "APP_VERSION" {
  type        = string
  description = "Version of the app to deploy"
}

variable "accounts" {
  type = map(
    object({
      account_id          = string
      prefix              = string
      behat_controller    = number
      sirius_api          = string
      sirius_bucket       = string
      is_production       = string
      dc_gtm              = string
      bucket_name         = string
      fixtures_enabled    = string
      sirius_key_alias    = string
      sirius_account      = string
      waf_enabled         = string
      ip_whitelist        = string
      deletion_protection = string
      postgres_version    = string
    })
  )
}

locals {
  environment = terraform.workspace
  account     = var.accounts[local.environment]
  management  = "311462405659"
}
