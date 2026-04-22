#!/bin/bash
#
# This script runs database migrations and seeders within the laravel_app docker container.
#
# Usage: ./migrate-db.sh [--seed] [--class=ClassName]


# Colors for terminal output
readonly RED="\033[31m"
readonly GREEN="\033[32m"
readonly YELLOW="\033[33m"
readonly BLUE="\033[34m"
readonly RESET="\033[0m"

# Shell safety options
set -o errexit
set -o nounset
set -o pipefail

# Print an error message to stderr.
# Arguments:
#   $@: Error message strings.
err() {
  printf "%b\n" "${RED}Error:${RESET} $*" >&2
}

# Print usage information and exit.
# Arguments:
#   None.
usage() {
  printf "Usage: %s [--seed] [--class=ClassName]\n" "$0"
}

# Parse command line arguments.
# Arguments:
#   $1: Name of the variable to set for seeding (0 or 1).
#   $2: Name of the variable to set for the seeder class name.
parse_args() {
  local -n _seed="$1"
  local -n _seed_class="$2"
  shift 2

  while [[ $# -gt 0 ]]; do
    case "$1" in
      --seed)
        _seed=1
        ;;
      --class=*)
        _seed_class="${1#*=}"
        ;;
      *)
        err "Unknown option: $1"
        usage
        exit 1
        ;;
    esac
    shift
  done
}

# Verify that the laravel_app container is healthy and running.
# Arguments:
#   None.
validate_container_status() {
  if [[ "$(docker inspect -f '{{.State.Running}}' laravel_app 2>/dev/null)" != "true" ]]; then
    err "The 'laravel_app' container is not running."
    printf "%b\n" "Please start your environment first, e.g.: ${YELLOW}./run_env.sh local-bindmount${RESET}"
    exit 1
  fi
}

# Execute database migrations inside the app container.
# Arguments:
#   None.
run_migrations() {
  printf "%b\n" "${BLUE}Running Database Migrations...${RESET}"
  
  # Note: Sourcing credentials before running artisan
  docker exec -u www-data laravel_app sh -c \
    ". /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan migrate --force"
}

# Execute database seeders inside the app container.
# Arguments:
#   $1: Seeding class name (optional).
run_seeders() {
  local -r seed_class="$1"
  printf "%b\n" "${BLUE}Seeding Database...${RESET}"
  
  local seed_cmd=". /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan db:seed"
  
  if [[ -n "${seed_class}" ]]; then
    seed_cmd="${seed_cmd} --class=${seed_class}"
  fi
  
  seed_cmd="${seed_cmd} --force"
  
  docker exec -u www-data laravel_app sh -c "${seed_cmd}"
}

# Main entry point for the database update lifecycle.
# Arguments:
#   $@: Raw command line arguments.
main() {
  # Argument parsing.
  local seed=0
  local seed_class=""
  parse_args seed seed_class "$@"

  validate_container_status

  run_migrations

  if [[ "${seed}" -eq 1 ]]; then
    run_seeders "${seed_class}"
  fi

  printf "%b\n" "${GREEN}Database update complete!${RESET}"
}

# Run the script.
main "$@"
