#!/bin/bash
#
# This script shuts down the Docker orchestration for the Laravel application.
# It removes all containers and cleans up ephemeral volumes.
#
# Usage: ./stop_env.sh


# Colors for terminal output
readonly RED="\033[31m"
readonly GREEN="\033[32m"
readonly YELLOW="\033[33m"
readonly BLUE="\033[34m"
readonly RESET="\033[0m"

# Project metadata
readonly PROJECT_NAME="laravel-starterkit"
readonly COMPOSE_FILE="compose.yaml"
# We define a base list of compose files.
declare -a COMPOSE_FILES=("-f" "${COMPOSE_FILE}")

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
  printf "Usage: %s\n" "$0"
}

# Parse command line arguments and validate no extra arguments are passed.
# Arguments:
#   $@: Raw command line arguments.
parse_args() {
  if [[ $# -gt 0 ]]; then
    usage
    exit 1
  fi
}

# Check if required system commands are available.
# Arguments:
#   None.
validate_environment() {
  if ! command -v docker >/dev/null 2>&1; then
    err "Required command 'docker' not found."
    exit 1
  fi
}

# Main entry point for the environment shutdown process.
# Arguments:
#   $@: Raw command line arguments.
main() {
  # Argument validation.
  parse_args "$@"

  validate_environment

  # We use a default .env file and profile to satisfy the Compose parser and ensure full cleanup
  local -r env_file="docker/.env.local-bindmount"
  local -r profile="all"

  printf "%b\n" "${BLUE}Removing existing containers and networks...${RESET}"
  docker compose -p "${PROJECT_NAME}" "${COMPOSE_FILES[@]}" --env-file "${env_file}" --profile "${profile}" down

  printf "%b\n" "${BLUE}Cleaning up application files volume...${RESET}"
  # (|| true to avoid error if volume doesn't exist)
  docker volume rm "${PROJECT_NAME}_app_files" >/dev/null 2>&1 || true

  printf "%b\n" "${GREEN}Success:${RESET} Environment has been stopped and cleaned."
}

# Run the script.
main "$@"