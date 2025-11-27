#!/bin/sh

ENV_FILE="docker/.env.production"
PROFILE="production"
COMPOSE_FILE="docker/docker-compose.production.yml"

# # Remove all volumes associated with this compose file (clean start)
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile all down -v

# # Run docker compose with the selected .env file and profile
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE run --rm --build certbot init

docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE up --build -d