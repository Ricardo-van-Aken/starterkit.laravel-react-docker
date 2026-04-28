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

  local -r profile="all"

  # Auto-detect the first available env file to suppress Docker Compose variable warnings.
  # Checked in priority order; falls back to no --env-file on a fresh server.
  local env_file=""
  local -r env_candidates=("docker/.env.production" "docker/.env.staging" "docker/.env.dev" "docker/.env.local")
  for candidate in "${env_candidates[@]}"; do
    if [[ -f "${candidate}" ]]; then
      env_file="${candidate}"
      break
    fi
  done

  local -a compose_down_args=(-p "${PROJECT_NAME}" "${COMPOSE_FILES[@]}" --profile "${profile}")
  [[ -n "${env_file}" ]] && compose_down_args+=(--env-file "${env_file}")

  printf "%b\n" "${BLUE}Removing existing containers and networks...${RESET}"
  docker compose "${compose_down_args[@]}" down

  printf "%b\n" "${BLUE}Cleaning up application files volume...${RESET}"
  # (|| true to avoid error if volume doesn't exist)
  docker volume rm "${PROJECT_NAME}_app_files" >/dev/null 2>&1 || true

  printf "%b\n" "${GREEN}Success:${RESET} Environment has been stopped and cleaned."
}

# Run the script.
main "$@"