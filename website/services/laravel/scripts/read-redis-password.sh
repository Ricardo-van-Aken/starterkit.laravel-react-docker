#!/bin/sh
set -e

# Read redis password from Docker secret and export as environment variable
if [ -f /run/secrets/redis_password ]; then
  REDIS_PASSWORD=$(cat /run/secrets/redis_password | tr -d '\r\n')
  export REDIS_PASSWORD
fi 