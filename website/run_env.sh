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

  # -----------------------------
  # THREAD A: laravel-base + app image
  # -----------------------------
  (
    set -e

    echo "==> Building laravel-base"

    # Read Dockerfile param from .env file. This param is used to decide which dockerfile will be used for laravel-app image
    DOCKERFILE=$(grep '^DOCKERFILE=' "$ENV_FILE" | cut -d '=' -f2-)
    if [ -z "$DOCKERFILE" ]; then
      echo "Error: DOCKERFILE not found in $ENV_FILE"
      exit 1
    fi

    # Build the base image with the host user's identifiers for image www-data user. This will resolve potential
    # permission issues when mounting host application files into the container.
    LOCAL_UID="$(id -u)"
    LOCAL_GID="$(id -g)"
    docker build \
      -f docker/img_laravel/Dockerfile.laravel-base \
      -t local/starterkit.laravel-react-docker:laravel-base.latest \
      --build-arg WWW_UID=$LOCAL_UID \
      --build-arg WWW_GID=$LOCAL_GID \
      . \
      2>&1 | log "laravel-base" "$GREEN"

    echo "==> Building laravel-app"

    # Build the app image using the base image and the selected Dockerfile
    docker build \
      -f docker/img_laravel/$DOCKERFILE \
      -t local/starterkit.laravel-react-docker:laravel-app.latest \
      --build-arg BASE_IMAGE=local/starterkit.laravel-react-docker:laravel-base.latest \
      . \
      2>&1 | log "laravel-app" "$BLUE"
  ) &
  APP_CHAIN_PID=$!

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
      phpredisadmin \
      2>&1 | log "compose" "$YELLOW"
  ) &
  COMPOSE_PID=$!

  wait $APP_CHAIN_PID || exit 1
  wait $COMPOSE_PID || exit 1

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