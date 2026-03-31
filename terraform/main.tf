# =============================================
# automateCRM — Terraform AWS Infrastructure
# Main configuration
# =============================================

terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }

  backend "s3" {
    bucket         = "automatecrm-terraform-state"
    key            = "infrastructure/terraform.tfstate"
    region         = "ap-southeast-1"
    dynamodb_table = "automatecrm-terraform-lock"
    encrypt        = true
  }
}

provider "aws" {
  region = var.aws_region

  default_tags {
    tags = {
      Project     = "automateCRM"
      Environment = var.environment
      ManagedBy   = "Terraform"
      Owner       = "rafii-afif"
    }
  }
}
