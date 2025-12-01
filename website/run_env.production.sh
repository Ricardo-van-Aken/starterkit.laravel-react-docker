#!/bin/sh

ENV_FILE="docker/.env.production"
PROFILE="production"
COMPOSE_FILE="docker/docker-compose.production.yml"

# Wind down containers and remove volumes
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE down -v

# Pull latest versions of images from registry
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE pull

# Prepare certificates
# [TODO] We should pull certbot interactivity from environment variable
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE run --rm certbot init --non-interactive

# Run docker compose with the selected .env file and profile
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE up -d