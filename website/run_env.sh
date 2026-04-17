#!/bin/sh

# This script runs docker-compose.yml with a selected profile and .env file.
# Usage: ./run_env.sh [local-volume|local-bindmount|mock-prod|staging|production]

RED="\033[31m"
GREEN="\033[32m"
YELLOW="\033[33m"
BLUE="\033[34m"
CYAN="\033[36m"
MAGENTA="\033[35m"
RESET="\033[0m"

# Check if required commands are available
for cmd in docker grep tput; do
  if ! command -v "$cmd" >/dev/null 2>&1; then
    echo "${RED}Error:${RESET} Required command '${YELLOW}$cmd${RESET}' not found. Please install it to continue."
    exit 1
  fi
done

# Check specifically for docker compose (V2)
if ! docker compose version >/dev/null 2>&1; then
  echo "${RED}Error:${RESET} '${YELLOW}docker compose${RESET}' (V2) is not available. Please install it to continue."
  exit 1
fi

# Get the mode from the first argument
MODE="$1"
PROJECT_NAME="laravel-starterkit"
COMPOSE_FILE="docker/docker-compose.yml"

# Show usage if no mode is provided
if [ -z "$MODE" ]; then
  echo "Usage: $0 [local-volume|local-bindmount|mock-prod|staging|production]"
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

# Retrieve/Build Images
if [ "$MODE" = "staging" ] || [ "$MODE" = "production" ]; then
  echo "Remote environment selected, pulling latest versions of images from registry..."
  docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE pull

elif [ "$MODE" = "local-volume" ] || [ "$MODE" = "local-bindmount" ] || [ "$MODE" = "mock-prod" ]; then
  echo "Local environment selected, building images locally..."
  
  # Export local user IDs so Docker Compose can pass them as build arguments
  export LOCAL_UID="$(id -u)"
  export LOCAL_GID="$(id -g)"

  docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE build --parallel
  if [ $? -ne 0 ]; then
    echo "${RED}Error:${RESET} Build failed."
    exit 1
  fi
fi

# Deployment
echo "Removing existing containers and networks."
docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile all down

echo "Removing the app_files volume to refresh the application files."
docker volume rm ${PROJECT_NAME}_app_files || true # (|| true to avoid error if volume doesn't exist on first run)

# Prepare first-time SSL certificates only for staging and production environments(the init command checks if certificates are already present)
# TODO: Run this only if certificates for the domains in APP_DOMAIN are not present, to prevent spinning up certbot container unnecessarily.
#       the entrypoint from certbot does a similar check, but we don't have access to the APP_DOMAIN variable from here.
if [ "$MODE" = "staging" ] || [ "$MODE" = "production" ]; then
  echo "Preparing SSL certificates..."
  docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE run --rm certbot init --non-interactive
fi

# Start new containers
echo "Running docker compose with the selected .env file and profile."
docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE up -d