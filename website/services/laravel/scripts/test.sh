#!/bin/bash
set -e

# Parse arguments
PARALLEL=false
CONFIG_FILE="phpunit.none.xml"
FILTER_ARG=""

while [[ "$#" -gt 0 ]]; do
  case "$1" in
    --parallel) PARALLEL=true; shift ;;
    -c|--configuration) CONFIG_FILE="$2"; shift 2 ;;
    --filter) FILTER_ARG="--filter $2"; shift 2 ;;
    *) echo "Unknown parameter passed: $1"; exit 1 ;;
  esac
done

# Run php artisan test as www-data
TEST_ARGS="-c $CONFIG_FILE"
if [ "$PARALLEL" = "true" ]; then
  TEST_ARGS="$TEST_ARGS --parallel"
fi
if [ -n "$FILTER_ARG" ]; then
  TEST_ARGS="$TEST_ARGS $FILTER_ARG"
fi

/bin/sh -c ". /usr/local/bin/read-app-key.sh && \
            . /usr/local/bin/read-db-credentials.sh && \
            . /usr/local/bin/read-redis-password.sh && \
            cd /var/www && \
            php artisan test $TEST_ARGS"