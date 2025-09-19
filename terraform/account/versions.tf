terraform {
  required_version = ">= 1.13.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = ">= 5.0.0"
    }
    local = {
      source  = "hashicorp/local"
      version = ">= 2.5.3"
    }
    archive = {
      source  = "hashicorp/archive"
      version = ">= 2.2.0"
    }
  }
}
