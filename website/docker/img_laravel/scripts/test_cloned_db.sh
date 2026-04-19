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

# Run the DB clone script, forwarding the ssl flag if set
CLONE_ARGS=""
if [ "$ALLOW_SELF_SIGNED_SSL" = "true" ]; then
  CLONE_ARGS="--allow-self-signed-ssl"
fi
/bin/sh -c "/usr/local/bin/clone_db_for_testing.sh $CLONE_ARGS"

# Run php artisan test, adding --parallel if requested
TEST_ARGS="-c $CONFIG_FILE"
if [ "$PARALLEL" = "true" ]; then
  TEST_ARGS="$TEST_ARGS --parallel"
fi
if [ -n "$FILTER_ARG" ]; then
  TEST_ARGS="$TEST_ARGS $FILTER_ARG"
fi
/bin/sh -c ". /usr/local/bin/read-db-credentials-testing.sh; . /usr/local/bin/read-redis-password.sh; cd /var/www && php artisan test $TEST_ARGS"