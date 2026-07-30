#!/bin/bash
set -e

# Parse arguments
PARALLEL=false
ALLOW_SELF_SIGNED_SSL=false
CONFIG_FILE="phpunit.isolated.xml"
FILTER_ARG=""

while [[ "$#" -gt 0 ]]; do
  case "$1" in
    --parallel) PARALLEL=true; shift ;;
    --allow-self-signed-ssl) ALLOW_SELF_SIGNED_SSL=true; shift ;;
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

# By default the database connection verifies the server's TLS certificate (production-like).
# Locally the MySQL certificates are self-signed, so --allow-self-signed-ssl relaxes verification
# for this run only. Remote environments (staging/production) must never pass this flag.
SSL_ENV=""
if [ "$ALLOW_SELF_SIGNED_SSL" = "true" ]; then
  SSL_ENV="export MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=false && "
fi

/bin/sh -c ". /usr/local/bin/read-app-key.sh && \
            . /usr/local/bin/read-db-credentials-testing.sh && \
            . /usr/local/bin/read-redis-password.sh && \
            ${SSL_ENV}cd /var/www && \
            php artisan test $TEST_ARGS"