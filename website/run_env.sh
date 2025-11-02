#!/bin/sh

# This script runs docker-compose.yml with a selected profile and .env file.
# Usage: ./run_env_profile.sh [dev-volume|dev-bindmount|fake-prod]

# Get the mode from the first argument
MODE="$1"

# Show usage if no mode is provided
if [ -z "$MODE" ]; then
  echo "Usage: $0 [dev-volume|dev-bindmount|fake-prod]"
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
  *)
    # Invalid mode provided
    echo "Invalid mode: $MODE"
    echo "Valid options: dev-volume, dev-bindmount, fake-prod"
    exit 1
    ;;
esac

# Build the base laravel application image if it doesn't exist or Dockerfile.base has changed
docker build -f docker/img_laravel/Dockerfile.laravel-base -t laravel-base:latest .

# Remove all volumes associated with this compose file (clean start)
docker-compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile all down -v

# Run docker-compose with the selected .env file and profile
docker-compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE up -d --build 