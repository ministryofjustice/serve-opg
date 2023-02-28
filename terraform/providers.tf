variable "DEFAULT_ROLE" {
  default = "serve-opg-ci"
}

variable "SIRIUS_ROLE" {
  default = "serve-assume-role-ci"
}

variable "SHARED_ROLE" {
  default = "serve-opg-ci"
}

provider "aws" {
  region = "eu-west-1"

  assume_role {
    role_arn     = "arn:aws:iam::${local.account_id}:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  region = "us-east-1"
  alias  = "us-east-1"

  assume_role {
    role_arn     = "arn:aws:iam::${local.account_id}:role/${var.DEFAULT_ROLE}"
    session_name = "terraform-session"
  }
}

provider "aws" {
  region = "eu-west-1"
  alias  = "management"

  assume_role {
    role_arn = "arn:aws:iam::${var.accounts["management"]}:role/${var.DEFAULT_ROLE}"
  }
}

provider "aws" {
  region = "eu-west-1"
  alias  = "sirius"

  assume_role {
    role_arn = "arn:aws:iam::${local.sirius_account}:role/${local.sirius_role}"
  }
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
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = ">= 4.56.0"
    }
  }
}

