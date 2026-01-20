#!/bin/sh

# This script runs docker-compose.yml down for all containers (using the 'all' profile)
# Usage: ./stop_env.sh

PROJECT_NAME="laravel-starterkit"
COMPOSE_FILE="docker/docker-compose.yml"

# Shut down existing containers
echo "Removing existing containers and networks."
docker compose -p $PROJECT_NAME -f $COMPOSE_FILE --profile all down

echo "Removing the app_files volume to refresh the application files."
docker volume rm ${PROJECT_NAME}_app_files || true # (|| true to avoid error if volume doesn't exist on first run)