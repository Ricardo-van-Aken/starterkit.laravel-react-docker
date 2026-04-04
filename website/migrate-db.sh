#!/bin/sh

# This script runs database migrations and seeders within the laravel_app docker container.

# Default values
SEED=0
SEED_CLASS=""

# Parse arguments
while [ "$#" -gt 0 ]; do
  case "$1" in
    --seed)
      SEED=1
      shift
      ;;
    --class=*)
      SEED_CLASS="${1#*=}"
      shift
      ;;
    *)
      echo "Unknown option: $1"
      exit 1
      ;;
  esac
done

# Check if the app container is running
if [ "$(docker inspect -f '{{.State.Running}}' laravel_app 2>/dev/null)" != "true" ]; then
  echo "\033[31mError: The 'laravel_app' container is not running.\033[0m"
  echo "Please start your environment first, e.g.: ./run_env.sh local-bindmount"
  exit 1
fi

echo "\033[34m==> Running Database Migrations\033[0m"
docker exec -u www-data laravel_app sh -c ". /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan migrate --force"

if [ $? -ne 0 ]; then
    echo "\033[31mError: Migrations failed.\033[0m"
    exit 1
fi

if [ "$SEED" -eq 1 ]; then
    echo "\033[34m==> Seeding Database\033[0m"
    
    SEED_CMD=". /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan db:seed"
    
    if [ -n "$SEED_CLASS" ]; then
        SEED_CMD="$SEED_CMD --class=$SEED_CLASS"
    fi
    
    SEED_CMD="$SEED_CMD --force"
    
    docker exec -u www-data laravel_app sh -c "$SEED_CMD"
    
    if [ $? -ne 0 ]; then
        echo "\033[31mError: Seeding failed.\033[0m"
        exit 1
    fi
fi

echo "\033[32m==> Database update complete!\033[0m"
