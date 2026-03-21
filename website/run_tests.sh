#!/bin/sh

set -euo pipefail

# Always run from the script's directory so relative paths work
SCRIPT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
cd "$SCRIPT_DIR"

# Start/refresh the testing environment containers
./run_env.sh mock-prod

# Run the Laravel test suite using the provided script inside the container
echo "Running tests via clone_and_test.sh..."
set +e
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-db-credentials.sh; php artisan migrate --force'
docker exec -u testrunner laravel_app sh -c '. /usr/local/bin/clone_db_for_testing.sh'
# docker exec -u testrunner laravel_app sh -c ". /usr/local/bin/read-db-credentials-testing.sh; . /usr/local/bin/read-redis-password.sh; cd /var/www && php artisan test"