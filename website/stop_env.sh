#!/bin/sh

# This script runs docker-compose.yml with a selected profile and .env file.
# Usage: ./run_env_profile.sh [dev-volume|dev-bindmount|fake-prod]

# Get the mode from the first argument
MODE="$1"

# Show usage if no mode is provided
if [ -z "$MODE" ]; then
  echo "Usage: $0 [dev-volume|dev-bindmount|fake-prod|production]"
  exit 1
fi

# Path to the main docker-compose file
COMPOSE_FILE="docker/docker-compose.yml"

# Select the appropriate .env file and docker-compose profile based on the mode
case "$MODE" in
  dev-volume)
    # For development with volume
    ENV_FILE="docker/.env.dev-volume"
    PROFILE="dev-volume"
    ;;
  dev-bindmount)
    # For development with bindmount
    ENV_FILE="docker/.env.dev-bindmount"
    PROFILE="dev-bindmount"
    ;;
  fake-prod)
    # For simulating production/testing
    ENV_FILE="docker/.env.testing"
    PROFILE="testing"
    ;;
  staging)
    # For simulating production/testing
    ENV_FILE="docker/.env.staging"
    PROFILE="staging"
    COMPOSE_FILE="docker-compose.yml"
    ;;
  production)
    # For simulating production/testing
    ENV_FILE="docker/.env.production"
    PROFILE="production"
    COMPOSE_FILE="docker-compose.yml"
    ;;
  *)
    # Invalid mode provided
    echo "Invalid mode: $MODE"
    echo "Valid options: dev-volume, dev-bindmount, fake-prod, staging"
    exit 1
    ;;
esac

# Shut down existing containers
echo "Removing existing containers and networks."
docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --env-file $ENV_FILE --profile all down

echo "Removing the app_files volume to refresh the application files."
docker volume rm ${PROJECT_NAME}_app_files || true # (|| true to avoid error if volume doesn't exist on first run)