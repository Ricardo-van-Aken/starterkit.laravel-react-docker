#!/usr/bin/env bash
set -euo pipefail

generate_backend_file() {
  local spaces_region="${1:-}"
  local bucket_name="${2:-}"
  local backend_file="${3:-backend.hcl}"

  if [[ -z "${bucket_name}" ]]; then
    echo "[ERROR] Bucket name is required to generate backend.hcl" >&2
    return 1
  fi
  
  echo "[INFO] Generating ${backend_file} with bucket name: ${bucket_name}" >&2
  
  cat > "${backend_file}" << EOF
# Shared backend configuration for DigitalOcean Spaces (S3-compatible)

endpoints = {
	s3 = "https://${spaces_region}.digitaloceanspaces.com"
}

bucket  = "${bucket_name}"
region  = "us-east-1"

# AWS-specific checks disabled for Spaces
skip_credentials_validation = true
skip_requesting_account_id  = true
skip_metadata_api_check     = true
skip_region_validation      = true
skip_s3_checksum            = true

# Enable lockfile-based state locking in Spaces
use_lockfile = true
EOF

  echo "[SUCCESS] Generated ${backend_file}" >&2
}