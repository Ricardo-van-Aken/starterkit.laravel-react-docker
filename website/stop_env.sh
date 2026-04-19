#!/bin/bash
#
# This script shuts down the Docker orchestration for the Laravel application.
# It removes all containers and cleans up ephemeral volumes.
#
# Usage: ./stop_env.sh

# -----------------------------------------------------------------------------
# CONSTANTS & CONFIGURATION
# -----------------------------------------------------------------------------

# Colors for terminal output
readonly YELLOW="\033[33m"
readonly GREEN="\033[32m"
readonly RESET="\033[0m"

# Project metadata
readonly PROJECT_NAME="laravel-starterkit"
readonly COMPOSE_FILE="docker/docker-compose.yml"

# Shell safety options
set -o errexit
set -o nounset
set -o pipefail

# -----------------------------------------------------------------------------
# HELPER FUNCTIONS
# -----------------------------------------------------------------------------

# Print usage information and exit.
usage() {
  echo "Usage: $0"
}

# Check if required system commands are available.
validate_environment() {
  if ! command -v docker >/dev/null 2>&1; then
    echo "Error: Required command 'docker' not found." >&2
    exit 1
  fi
}

# -----------------------------------------------------------------------------
# MAIN EXECUTION
# -----------------------------------------------------------------------------

main() {
  # Argument validation.
  if [[ $# -gt 0 ]]; then
    usage
    exit 1
  fi

  validate_environment

  # We use a default .env file and profile to satisfy the Compose parser and ensure full cleanup
  local -r env_file="docker/.env.local-bindmount"
  local -r profile="all"

  echo -e "${YELLOW}Removing existing containers and networks...${RESET}"
  docker compose -p "${PROJECT_NAME}" -f "${COMPOSE_FILE}" --env-file "${env_file}" --profile "${profile}" down

  echo -e "${YELLOW}Cleaning up application files volume...${RESET}"
  # (|| true to avoid error if volume doesn't exist)
  docker volume rm "${PROJECT_NAME}_app_files" >/dev/null 2>&1 || true

  echo -e "${GREEN}Success:${RESET} Environment has been stopped and cleaned."
}

# Run the script.
main "$@"