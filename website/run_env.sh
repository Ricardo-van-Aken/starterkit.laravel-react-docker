#!/bin/bash
#
# This script manages the Docker Compose orchestration for the Laravel application.
# It handles profile selection, image building/pulling, and deployment lifecycle.
#
# Usage: ./run_env.sh [local-volume|local-bindmount|mock-prod|staging|production]

# -----------------------------------------------------------------------------
# CONSTANTS & CONFIGURATION
# -----------------------------------------------------------------------------

# Colors for terminal output
readonly RED="\033[31m"
readonly GREEN="\033[32m"
readonly YELLOW="\033[33m"
readonly BLUE="\033[34m"
readonly RESET="\033[0m"

# Project metadata
readonly PROJECT_NAME="laravel-starterkit"
readonly COMPOSE_FILE="docker-compose.yml"

# Shell safety options
set -o errexit
set -o nounset
set -o pipefail

# -----------------------------------------------------------------------------
# HELPER FUNCTIONS
# -----------------------------------------------------------------------------

# Print an error message to stderr.
# Arguments:
#   $@: Error message strings.
err() {
  echo -e "${RED}Error:${RESET} $*" >&2
}

# Print usage information and exit.
usage() {
  echo "Usage: $0 [--log] [local-volume|local-bindmount|mock-prod|staging|production]"
}

# Check if required system commands are available.
validate_environment() {
  local -r required_cmds=("docker" "grep" "tput" "id")
  local cmd
  for cmd in "${required_cmds[@]}"; do
    if ! command -v "${cmd}" >/dev/null 2>&1; then
      err "Required command '${YELLOW}${cmd}${RESET}' not found. Please install it to continue."
      exit 1
    fi
  done

  # Verify Docker Compose V2 specifically.
  if ! docker compose version >/dev/null 2>&1; then
    err "'${YELLOW}docker compose${RESET}' (V2) is not available. Please install it."
    exit 1
  fi
}

# Map the selected mode to specific environment files and compose profiles.
# Arguments:
#   $1: The selected mode string.
# Outputs:
#   Sets env_file and profile variables.
select_mode_config() {
  local -r mode="$1"
  env_file=""
  profile=""

  case "${mode}" in
    local-volume)
      # Local development with volume
      env_file="docker/.env.local-volume"
      profile="local-volume"
      ;;
    local-bindmount)
      # Local development with bindmount
      env_file="docker/.env.local-bindmount"
      profile="local-bindmount"
      ;;
    mock-prod)
      # Simulates production locally for testing purposes
      env_file="docker/.env.mock-prod"
      profile="mock-prod"
      ;;
    staging)
      # Staging, should be run on real infrastructure, as close to production as possible
      env_file="docker/.env.staging"
      profile="staging"
      ;;
    production)
      # Production, should be run on the production infrastructure
      env_file="docker/.env.production"
      profile="production"
      ;;
    *)
      # Invalid mode provided
      err "Invalid mode: ${mode}"
      usage
      exit 1
      ;;
  esac
}

# Process raw BuildKit logs into a grouped, structured format.
# Arguments:
#   $1: Path to raw log file.
#   $2: Path to final structured log file.
process_build_logs() {
  local -r raw_log="$1"
  local -r final_log="$2"
  local -r awk_script="process_logs.awk"

  echo -e "${YELLOW}Structuring build logs by stage...${RESET}"
  
  if [[ ! -f "${awk_script}" ]]; then
    err "Log processing script not found at ${awk_script}"
    cat "${raw_log}" > "${final_log}"
    return
  fi

  awk -f "${awk_script}" "${raw_log}" > "${final_log}"
}

# Perform image staging based on the environment type.
# Arguments:
#   $1: Selected mode.
#   $2: Env file path.
#   $3: Compose profile.
prepare_images() {
  local -r mode="$1"
  local -r env_file="$2"
  local -r profile="$3"
  local -r log_to_file="$4"
  local -r raw_log="build_raw.log"
  local -r final_log="build.log"

  if [[ "${mode}" == "staging" || "${mode}" == "production" ]]; then
    echo -e "${BLUE}Remote environment selected. Pulling latest images...${RESET}"
    docker compose -p "${PROJECT_NAME}" -f "${COMPOSE_FILE}" --env-file "${env_file}" --profile "${profile}" pull
  else
    echo -e "${BLUE}Local environment selected. Building images locally...${RESET}"
    
    # Export local user IDs for the Docker build context.
    export LOCAL_UID="$(id -u)"
    export LOCAL_GID="$(id -g)"

    if [[ "${log_to_file}" == "true" ]]; then
      # Initialize/Clear log files
      mkdir -p "$(dirname "$final_log")"
      : > "$final_log"

      # Build while silencing terminal and capturing raw logs
      if ! docker compose -p "${PROJECT_NAME}" -f "${COMPOSE_FILE}" --env-file "${env_file}" --profile "${profile}" build --parallel > "$raw_log" 2>&1; then
        err "Build failed. Consult \"$raw_log\" for details."
        exit 1
      fi

      # Group the logs by stage
      process_build_logs "$raw_log" "$final_log"
      rm "$raw_log"
    else
      # Standard interactive build in terminal
      if ! docker compose -p "${PROJECT_NAME}" -f "${COMPOSE_FILE}" --env-file "${env_file}" --profile "${profile}" build --parallel; then
        err "Build failed."
        exit 1
      fi
    fi
  fi
}

# Tear down previous container states and volumes to ensure a clean deployment.
# Arguments:
#   $1: Env file path.
cleanup_previous() {
  local -r env_file="$1"

  echo -e "${YELLOW}Removing existing containers and networks...${RESET}"
  docker compose -p "${PROJECT_NAME}" -f "${COMPOSE_FILE}" --env-file "${env_file}" --profile all down

  echo -e "${YELLOW}Refreshing application volumes...${RESET}"
  docker volume rm "${PROJECT_NAME}_app_files" >/dev/null 2>&1 || true
}

# Finalize orchestration and start the containers.
# Arguments:
#   $1: Selected mode.
#   $2: Env file path.
#   $3: Compose profile.
deploy() {
  local -r mode="$1"
  local -r env_file="$2"
  local -r profile="$3"

  # Initialize SSL certificates for remote environments if necessary.
  if [[ "${mode}" == "staging" || "${mode}" == "production" ]]; then
    echo -e "${BLUE}Initializing SSL certificates...${RESET}"
    docker compose -p "${PROJECT_NAME}" -f "${COMPOSE_FILE}" --env-file "${env_file}" --profile "${profile}" run --rm certbot init --non-interactive
  fi

  echo -e "${GREEN}Deployment started: Running docker compose up...${RESET}"
  docker compose -p "${PROJECT_NAME}" -f "${COMPOSE_FILE}" --env-file "${env_file}" --profile "${profile}" up -d
}

# -----------------------------------------------------------------------------
# MAIN EXECUTION
# -----------------------------------------------------------------------------

main() {
  validate_environment

  local selected_mode=""
  local log_to_file=false
  local env_file=""
  local profile=""

  # Argument parsing
  while [[ $# -gt 0 ]]; do
    case "$1" in
      -l|--log)
        log_to_file=true
        shift
        ;;
      local-volume|local-bindmount|mock-prod|staging|production)
        if [[ -n "${selected_mode}" ]]; then
          err "Multiple modes specified: ${selected_mode} and $1"
          usage
          exit 1
        fi
        selected_mode="$1"
        shift
        ;;
      *)
        err "Unexpected argument: $1"
        usage
        exit 1
        ;;
    esac
  done

  # Finalize configuration.
  if [[ -z "${selected_mode}" ]]; then
    usage
    exit 1
  fi
  select_mode_config "${selected_mode}"

  local -r start_time=$(date +%s)
  
  # Execution flow.
  local -r build_start=$(date +%s)
  prepare_images "${selected_mode}" "${env_file}" "${profile}" "${log_to_file}"
  local -r build_end=$(date +%s)
  local -r build_duration=$((build_end - build_start))

  cleanup_previous "${env_file}"
  deploy "${selected_mode}" "${env_file}" "${profile}"
  
  local -r end_time=$(date +%s)
  local -r total_duration=$((end_time - start_time))
  
  echo -e "${GREEN}Success:${RESET} Environment ${selected_mode} is ready."
  echo -e "${BLUE}Docker build duration:${RESET} ${build_duration}s"
  echo -e "${BLUE}Total duration:${RESET} ${total_duration}s"
}

# Run the script.
main "$@"