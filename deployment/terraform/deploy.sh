#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

cd "$ROOT/bootstrap"

terraform init -input=false
terraform apply

# capture outputs
export TF_VAR_spaces_access_id=$(terraform output -raw spaces_access_id)
export TF_VAR_spaces_secret_key=$(terraform output -raw spaces_secret_key)

cd "$ROOT/main"

terraform init -input=false
terraform apply

# cleanup sensitive env
unset TF_VAR_spaces_access_id TF_VAR_spaces_secret_key

cd "$ROOT/bootstrap"

terraform destroy -auto-approve