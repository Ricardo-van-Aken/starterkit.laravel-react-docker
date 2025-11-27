#!/bin/sh

ENV_FILE="docker/.env.production"
PROFILE="production"
COMPOSE_FILE="docker/docker-compose.production.yml"

# Remove all volumes associated with this compose file (clean start)
docker volume rm docker_app_files

docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE pull

# Run docker compose with the selected .env file and profile
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE run --rm certbot init

docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE up -d