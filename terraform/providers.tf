variable "DEFAULT_ROLE" {
  default = "serve-opg-ci"
  type = string
}

variable "SIRIUS_ROLE" {
  default = "serve-assume-role-ci"
  type = string
}

terraform {
  backend "s3" {
    bucket         = "opg.terraform.state"
    key            = "serve-opg-infrastructure/terraform.tfstate"
    encrypt        = true
    region         = "eu-west-1"
    role_arn       = "arn:aws:iam::311462405659:role/serve-opg-ci"
    dynamodb_table = "remote_lock"
  }
}

provider "aws" {
  region = "eu-west-1"

  assume_role {
    role_arn     = "arn:aws:iam::${local.account.account_id}:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  alias  = "us-east-1"
  region = "us-east-1"

  assume_role {
    role_arn     = "arn:aws:iam::${local.account.account_id}:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  alias  = "management"
  region = "eu-west-1"

  assume_role {
    role_arn = "arn:aws:iam::${local.management}:role/${var.DEFAULT_ROLE}"
  }
}

provider "aws" {
  alias  = "sirius"
  region = "eu-west-1"

  assume_role {
    role_arn = "arn:aws:iam::${local.account.sirius_account}:role/${local.sirius_role}"
  }
}
