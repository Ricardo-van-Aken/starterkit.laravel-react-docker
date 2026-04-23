#!/bin/sh
set -e

# Source APP_KEY from Docker secrets
if [ -f /run/secrets/app_key ]; then
  APP_KEY=$(cat /run/secrets/app_key | tr -d '\r\n')
  export APP_KEY
fi
