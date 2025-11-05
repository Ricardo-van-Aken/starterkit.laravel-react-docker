terraform {
  required_version = ">= 1.6.3"

  backend "s3" {
    # # Shared backend configuration for DigitalOcean Spaces (S3-compatible)

    # endpoints = {
    #   s3 = "https://ams3.digitaloceanspaces.com"
    # }

    # bucket = "terraform-state-bucket-dwauydaf"
    # region = "us-east-1"

    # # AWS-specific checks disabled for Spaces
    # skip_credentials_validation = true
    # skip_requesting_account_id  = true
    # skip_metadata_api_check     = true
    # skip_region_validation      = true
    # skip_s3_checksum            = true

    # workspace_key_prefix = "projects/starterkit.laravel-react-docker/infra-bootstrap"
    # key                  = "terraform.tfstate"

    # # Enable lockfile-based state locking in Spaces
    # use_lockfile = true
  }
}
