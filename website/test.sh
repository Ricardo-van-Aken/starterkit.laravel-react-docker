#!/bin/bash
#
# This script runs the test suite within the laravel_app docker container.
# Any flags passed to this script are forwarded verbatim to the container's
# /usr/local/bin/test.sh (e.g. --parallel, --allow-self-signed-ssl,
# -c <config>, --filter <name>).
#
# Usage: ./test.sh [test.sh flags...]
#   ./test.sh                                  # single thread, verifies DB SSL certs
#   ./test.sh --allow-self-signed-ssl          # single thread, self-signed certs (local dev)
#   ./test.sh --parallel                       # parallel, verifies DB SSL certs
#   ./test.sh --parallel --allow-self-signed-ssl
#   ./test.sh --filter SomeTest                # run a subset of tests


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

# Run the test suite inside the app container, forwarding all arguments.
# Arguments:
#   $@: Flags to forward to the container's test.sh.
run_tests() {
  printf "%b\n" "${BLUE}Running tests...${RESET}"

  docker exec -u testrunner laravel_app bash /usr/local/bin/test.sh "$@"
}

# Main entry point.
# Arguments:
#   $@: Raw command line arguments, forwarded to the container's test.sh.
main() {
  validate_container_status

  run_tests "$@"

  printf "%b\n" "${GREEN}Tests complete!${RESET}"
}

# Run the script.
main "$@"
