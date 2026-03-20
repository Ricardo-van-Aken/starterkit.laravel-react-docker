#!/bin/bash
set -e

# Parse arguments
PARALLEL=false
ALLOW_SELF_SIGNED_SSL=false
for arg in "$@"; do
  case "$arg" in
    --parallel)            PARALLEL=true ;;
    --allow-self-signed-ssl) ALLOW_SELF_SIGNED_SSL=true ;;
  esac
done

# Run the DB clone script, forwarding the ssl flag if set
CLONE_ARGS=""
if [ "$ALLOW_SELF_SIGNED_SSL" = "true" ]; then
  CLONE_ARGS="--allow-self-signed-ssl"
fi
/bin/sh -c "/usr/local/bin/clone_db_for_testing.sh $CLONE_ARGS"

# Run php artisan test, adding --parallel if requested
TEST_ARGS="-c phpunit.isolated.xml"
if [ "$PARALLEL" = "true" ]; then
  TEST_ARGS="$TEST_ARGS --parallel"
fi
/bin/sh -c ". /usr/local/bin/read-db-credentials-testing.sh; . /usr/local/bin/read-redis-password.sh; cd /var/www && php artisan test $TEST_ARGS"