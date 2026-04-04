#!/bin/sh

# This script runs docker-compose.yml with a selected profile and .env file.
# Usage: ./run_env_profile.sh [dev-volume|dev-bindmount|testing|staging|production]

log() {
  NAME="$1"
  COLOR="$2"

  awk -v name="$NAME" -v color="$COLOR" '
  BEGIN {
    reset="\033[0m"
  }
  {
    print color "[" name "]" reset " " $0
  }'
}
RED="\033[31m"
GREEN="\033[32m"
YELLOW="\033[33m"
BLUE="\033[34m"
CYAN="\033[36m"
MAGENTA="\033[35m"

# Get the mode from the first argument
MODE="$1"
PROJECT_NAME="laravel-starterkit"

# Path to the main docker-compose file
COMPOSE_FILE="docker/docker-compose.yml"

# Show usage if no mode is provided
if [ -z "$MODE" ]; then
  echo "Usage: $0 [dev-volume|dev-bindmount|testing|staging|production]"
  exit 1
fi

# Select the appropriate .env file and docker-compose profile based on the mode
case "$MODE" in
  local-volume)
    # Local development with volume
    ENV_FILE="docker/.env.local-volume"
    PROFILE="local-volume"
    ;;
  local-bindmount)
    # Local development with bindmount
    ENV_FILE="docker/.env.local-bindmount"
    PROFILE="local-bindmount"
    ;;
  mock-prod)
    # Simulates production locally for testing purposes
    ENV_FILE="docker/.env.mock-prod"
    PROFILE="mock-prod"
    ;;
  staging)
    # Staging, should be run on real infrastructure, as close to production as possible
    ENV_FILE="docker/.env.staging"
    PROFILE="staging"
    ;;
  production)
    # Production, should be run on the production infrastructure
    ENV_FILE="docker/.env.production"
    PROFILE="production"
    ;;
  *)
    # Invalid mode provided
    echo "Invalid mode: $MODE"
    echo "Valid options: local-volume, local-bindmount, mock-prod, staging, production"
    exit 1
    ;;
esac

# Retrieve Images
if [ "$MODE" = "staging" ] || [ "$MODE" = "production" ]; then
  echo "Remote environment selected, pulling latest versions of images from registry..."
  docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE pull

elif [ "$MODE" = "local-volume" ] || [ "$MODE" = "local-bindmount" ] || [ "$MODE" = "mock-prod" ]; then
  echo "Local environment selected, building images locally..."

  # Define log files
  LOG_APP="build_app.log"
  LOG_COMPOSE="build_compose.log"
  > "$LOG_APP"
  > "$LOG_COMPOSE"

  # -----------------------------
  # THREAD A: laravel-base + app image
  # -----------------------------
  (
    set -e

    # Read Dockerfile param from .env file. This param is used to decide which dockerfile will be used for laravel-app image
    DOCKERFILE=$(grep '^DOCKERFILE=' "$ENV_FILE" | cut -d '=' -f2-)
    if [ -z "$DOCKERFILE" ]; then
      echo "Error: DOCKERFILE not found in $ENV_FILE" >> "$LOG_APP"
      exit 1
    fi

    # Build the base image
    LOCAL_UID="$(id -u)"
    LOCAL_GID="$(id -g)"
    echo "==> Building laravel-base" >> "$LOG_APP"
    docker build \
      -f docker/img_laravel/Dockerfile.laravel-base \
      -t local/starterkit.laravel-react-docker:laravel-base.latest \
      --build-arg WWW_UID=$LOCAL_UID \
      --build-arg WWW_GID=$LOCAL_GID \
      . >> "$LOG_APP" 2>&1

    echo "==> Building laravel-app" >> "$LOG_APP"
    # Build the app image using the base image and the selected Dockerfile
    docker build \
      -f docker/img_laravel/$DOCKERFILE \
      -t local/starterkit.laravel-react-docker:laravel-app.latest \
      --build-arg BASE_IMAGE=local/starterkit.laravel-react-docker:laravel-base.latest \
      . >> "$LOG_APP" 2>&1
  ) &
  PID_APP_BUILD=$!

  # -----------------------------
  # THREAD B: compose services (parallel)
  # -----------------------------
  (
    set -e

    docker compose \
      -p $PROJECT_NAME \
      -f $COMPOSE_FILE \
      --env-file $ENV_FILE \
      --profile $PROFILE \
      build --parallel \
      nginx \
      redis \
      mysql_db \
      certbot \
      cert_local \
      phpmyadmin-admin \
      phpmyadmin-testing \
      phpredisadmin >> "$LOG_COMPOSE" 2>&1
  ) &
  PID_COMPOSE_BUILD=$!



  # -----------------------------
  # LIVE UPDATE LOOP
  # -----------------------------

  # Initialize state variables
  EXIT_APP=""
  EXIT_COMPOSE=""
  STATUS_APP="${BLUE}[laravel-app]${RESET} Pending..."
  STATUS_COMPOSE="${BLUE}[compose]${RESET} Pending..."

  # RESET variable for ANSI codes
  RESET="\033[0m"

  # Print placeholders for the status lines
  echo ""
  echo ""

  # Live update loop
  while [ -z "$EXIT_APP" ] || [ -z "$EXIT_COMPOSE" ]; do
    printf "\033[2A" # Back up 2 lines

    COLS=$(tput cols 2>/dev/null || echo 80)

    # Update app build status
    if [ -z "$EXIT_APP" ]; then
      if kill -0 $PID_APP_BUILD 2>/dev/null; then
        LINE_A=$(grep -E "^#[0-9]+ \[" "$LOG_APP" 2>/dev/null | tail -n 1 | cut -c1-$((COLS - 20)))
        STATUS_APP="${BLUE}[laravel-app]${RESET} ${LINE_A}"
      else
        wait $PID_APP_BUILD
        EXIT_APP=$?
        if [ "$EXIT_APP" -eq 0 ]; then
          STATUS_APP="${GREEN}[laravel-app]${RESET} Success!"
        else
          STATUS_APP="${RED}[laravel-app]${RESET} Failed! Check $LOG_APP"
        fi
      fi
    fi

    # Update compose build status
    if [ -z "$EXIT_COMPOSE" ]; then
      if kill -0 $PID_COMPOSE_BUILD 2>/dev/null; then
        LINE_B=$(grep -E "^#[0-9]+ \[" "$LOG_COMPOSE" 2>/dev/null | tail -n 1 | cut -c1-$((COLS - 20)))
        STATUS_COMPOSE="${BLUE}[compose]${RESET} ${LINE_B}"
      else
        wait $PID_COMPOSE_BUILD
        EXIT_COMPOSE=$?
        if [ "$EXIT_COMPOSE" -eq 0 ]; then
          STATUS_COMPOSE="${GREEN}[compose]${RESET} Success!"
        else
          STATUS_COMPOSE="${RED}[compose]${RESET} Failed! Check $LOG_COMPOSE"
        fi
      fi
    fi

    printf "\033[2K${STATUS_APP}\n"
    printf "\033[2K${STATUS_COMPOSE}\n"

    # Exit loop if both are finished
    [ -n "$EXIT_APP" ] && [ -n "$EXIT_COMPOSE" ] && break

    sleep 0.2
  done

  # Exit with error code if either failed
  if [ "$EXIT_APP" -ne 0 ] || [ "$EXIT_COMPOSE" -ne 0 ]; then
    exit 1
  fi

fi

# Shut down existing containers
echo "Removing existing containers and networks."
docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile all down

echo "Removing the app_files volume to refresh the application files."
docker volume rm ${PROJECT_NAME}_app_files || true # (|| true to avoid error if volume doesn't exist on first run)

# Prepare first-time SSL certificates only for staging and production environments(the init command checks if certificates are already present)
# TODO: Run this only if certificates for the domains in APP_DOMAIN are not present, to prevent spinning up certbot container unnecessarily.
#       the entrypoint from certbot does a similar check, but we don't have access to the APP_DOMAIN variable from here.
if [ "$MODE" = "staging" ] || [ "$MODE" = "production" ]; then
  echo "Preparing SSL certificates"
  docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE run --rm certbot init --non-interactive
fi

# Start new containers
echo "Running docker compose with the selected .env file and profile."
docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE up -d