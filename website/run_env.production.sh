#!/bin/sh

ENV_FILE="docker/.env.production"
PROFILE="production"
COMPOSE_FILE="docker/docker-compose.production.yml"

# Turn off containers
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE down

# Remove all app files [TODO: Dit willen we niet hardcoden op docker_app_files]
docker volume rm docker_app_files

docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE pull

# Run docker compose with the selected .env file and profile
docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE run --rm certbot init --non-interactive

docker compose -f $COMPOSE_FILE --env-file $ENV_FILE --profile $PROFILE up -d