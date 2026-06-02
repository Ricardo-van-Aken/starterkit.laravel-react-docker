#!/bin/bash
#
# This script manages the Docker Compose orchestration for the Laravel application.
# It handles profile selection, image building/pulling, and deployment lifecycle.
#
# Usage: ./run_env.sh [local-volume|local-bindmount|dev|staging|production]

readonly RED="\033[31m"
readonly GREEN="\033[32m"
readonly YELLOW="\033[33m"
readonly BLUE="\033[34m"
readonly RESET="\033[0m"

readonly PROJECT_NAME="laravel-starterkit"

readonly RAW_LOG="build_raw.log"
readonly FINAL_LOG="build.log"
readonly LOG_AWK_SCRIPT="process_logs.awk"

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
  printf "Usage: %s [--log] [local-volume|local-bindmount|dev|staging|production]\n" "$0"
}

# Parse command line arguments.
# Arguments:
#   $1: Name of the variable to set for the selected mode.
#   $2: Name of the variable to set for the log file flag.
#   $@: Remaining script arguments.
parse_args() {
  local -n _mode="$1"
  local -n _log="$2"
  shift 2

  while [[ $# -gt 0 ]]; do
    case "$1" in
      --log)
        _log=true
        ;;
      local-volume|local-bindmount|local-production|dev|staging|production)
        if [[ -n "${_mode}" ]]; then
          err "Mode already set to '${_mode}', cannot set to '${1}'"
          usage
          exit 1
        fi
        _mode="$1"
        ;;
      *)
        err "Unknown option: $1"
        usage
        exit 1
        ;;
    esac
    shift
  done

  if [[ -z "${_mode}" ]]; then
    usage
    exit 1
  fi
}

# Check if required system commands and Docker versions are available.
# Arguments:
#   None.
validate_environment() {
  local -r required_cmds=("docker" "awk" "date" "grep" "mkdir" "rm" "dirname" "cat" "tput" "id")
  local validation_failed=false

  for cmd in "${required_cmds[@]}"; do
    if ! command -v "${cmd}" >/dev/null 2>&1; then
      err "Required command '${YELLOW}${cmd}${RESET}' not found."
      validation_failed=true
    fi
  done

  # Verify Docker Compose V2 specifically.
  if ! docker compose version >/dev/null 2>&1; then
    err "Required Docker plugin '${YELLOW}docker-compose${RESET}' (V2) not found."
    validation_failed=true
  fi

  # Verify Docker Buildx plugin.
  if ! docker buildx version >/dev/null 2>&1; then
    err "Required Docker plugin '${YELLOW}docker-buildx${RESET}' not found."
    validation_failed=true
  fi

  if [[ "${validation_failed}" == true ]]; then
    exit 1
  fi
}

# Map the selected mode to specific environment files and compose profiles.
# Arguments:
#   $1: The selected mode string.
#   $2: Name of the variable to set for env_file.
#   $3: Name of the variable to set for profile.
#   $4: Name of the array variable for compose_files.
select_mode_config() {
  local -r mode="$1"
  local -n _env_file="$2"
  local -n _profile="$3"
  local -n _compose_files="$4"

  _env_file=""
  _profile=""
  _compose_files=("-f" "compose.yaml")

  case "${mode}" in
    local-volume)
      # Local development, dev image, named volume
      _env_file="docker/.env.local-dev"
      _profile="local"
      _compose_files+=("-f" "compose.dev.yaml")
      ;;
    local-bindmount)
      # Local development, dev image, host bindmount
      _env_file="docker/.env.local-dev"
      _profile="local"
      _compose_files+=("-f" "compose.dev.yaml" "-f" "compose.bindmount.yaml")
      ;;
    local-production)
      # Local development, production image, named volume
      _env_file="docker/.env.local-dev"
      _profile="local"
      ;;
    dev)
      # Remote environment, dev image, named volume. Domain: dev.<domainname>
      _env_file="docker/.env.remote-dev"
      _profile="remote"
      _compose_files+=("-f" "compose.dev.yaml")
      ;;
    staging)
      # Remote environment, production image, named volume. Domain: staging.<domainname>
      _env_file="docker/.env.remote-staging"
      _profile="remote"
      ;;
    production)
      # Remote environment, production image, named volume. Domain: <domainname>
      _env_file="docker/.env.remote-production"
      _profile="remote"
      ;;
    *)
      # Invalid mode provided
      err "Invalid mode: ${mode}"
      usage
      exit 1
      ;;
  esac
}

# Process raw BuildKit logs into a grouped, structured format using AWK.
# Arguments:
#   None.
process_build_logs() {
  printf "%b\n" "${BLUE}Structuring build logs by stage...${RESET}"
  
  if [[ ! -f "${LOG_AWK_SCRIPT}" ]]; then
    err "Log processing script not found at ${LOG_AWK_SCRIPT}"
    cat "${RAW_LOG}" > "${FINAL_LOG}"
    return
  fi

  awk -f "${LOG_AWK_SCRIPT}" "${RAW_LOG}" > "${FINAL_LOG}"
}

# Perform image staging (pulling or building) based on the environment type.
# Arguments:
#   $3: Compose profile.
#   $4: Boolean flag to log output to file.
#   $@: Compose files.
prepare_images() {
  local -r mode="$1"
  local -r env_file="$2"
  local -r profile="$3"
  local -r log_to_file="$4"
  shift 4

  if [[ "${mode}" == "dev" || "${mode}" == "staging" || "${mode}" == "production" ]]; then
    printf "%b\n" "${BLUE}Pulling images...${RESET}"
    docker compose -p "${PROJECT_NAME}" "$@" --env-file "${env_file}" --profile "${profile}" pull
  else
    printf "%b\n" "${BLUE}Building images...${RESET}"
    
    # Export env file variables for Bake interpolation
    set -o allexport
    source "${env_file}"
    set +o allexport

    if [[ "${log_to_file}" == "true" ]]; then
      # Initialize/Clear log files
      mkdir -p "$(dirname "$FINAL_LOG")"
      : > "$FINAL_LOG"

      # Build while silencing terminal and capturing raw logs
      printf "%b\n" "${YELLOW}Build will be logged to $FINAL_LOG${RESET}"
      
      if ! docker buildx bake "$@" --load > "$RAW_LOG" 2>&1; then
        err "Build failed. Consult \"$RAW_LOG\" for details."
        exit 1
      fi

      # Group the logs by stage
      process_build_logs
      rm "$RAW_LOG"
    else
      # Standard interactive build in terminal
      if ! docker buildx bake "$@" --load; then
        err "Build failed."
        exit 1
      fi
    fi
  fi
}



# Tear down previous container states and ephemeral volumes.
# Arguments:
#   $1: Env file path.
#   $@: Compose files.
cleanup_previous() {
  local -r env_file="$1"
  shift
  
  printf "%b\n" "${BLUE}Removing existing containers and networks...${RESET}"
  docker compose -p "${PROJECT_NAME}" "$@" --env-file "${env_file}" --profile all down

  printf "%b\n" "${BLUE}Refreshing application volumes...${RESET}"
  docker volume rm "${PROJECT_NAME}_app_files" >/dev/null 2>&1 || true
}


# Finalize orchestration and start the production or development containers.
# Arguments:
#   $1: Selected mode string.
#   $2: Env file path.
#   $3: Compose profile.
#   $@: Compose files.
deploy() {
  local -r mode="$1"
  local -r env_file="$2"
  local -r profile="$3"
  shift 3

  # Initialize SSL certificates for remote environments if necessary.
  if [[ "${mode}" == "dev" || "${mode}" == "staging" || "${mode}" == "production" ]]; then
    printf "%b\n" "${BLUE}Initializing SSL certificates...${RESET}"
    docker compose -p "${PROJECT_NAME}" "$@" --env-file "${env_file}" --profile "${profile}" run --rm certbot init --non-interactive
  fi

  printf "%b\n" "${BLUE}Deployment started: Running docker compose up...${RESET}"
  docker compose -p "${PROJECT_NAME}" "$@" --env-file "${env_file}" --profile "${profile}" up -d
}

# Main entry point for the orchestration lifecycle.
# Arguments:
#   $@: Raw command line arguments.
main() {
  validate_environment

  local -r start_time=$(date +%s)
  
  # Export local user IDs for the Docker build context.
  # This prevents warnings when variables are used in compose files.
  export LOCAL_UID="$(id -u)"
  export LOCAL_GID="$(id -g)"

  # Argument parsing
  local selected_mode=""
  local log_to_file=false
  parse_args selected_mode log_to_file "$@"
  
  local env_file
  local profile
  local -a compose_files
  select_mode_config "${selected_mode}" env_file profile compose_files
  
  # Start container cleanup in the background.
  cleanup_previous "${env_file}" "${compose_files[@]}" &
  local -r cleanup_pid=$!

  # Build or pull images.
  local -r build_start=$(date +%s)
  prepare_images "${selected_mode}" "${env_file}" "${profile}" "${log_to_file}" "${compose_files[@]}"
  local -r build_duration=$(( $(date +%s) - build_start ))

  # Wait for cleanup to finish before starting new containers.
  wait "${cleanup_pid}"
  
  deploy "${selected_mode}" "${env_file}" "${profile}" "${compose_files[@]}"
  local -r total_duration=$(( $(date +%s) - start_time ))
  
  printf "%b\n" "${GREEN}Success:${RESET} Environment ${selected_mode} is ready."
  printf "%b\n" "${BLUE}Docker build duration:${RESET} ${build_duration}s"
  printf "%b\n" "${BLUE}Total duration:${RESET} ${total_duration}s"
}

# Run the script.
main "$@"