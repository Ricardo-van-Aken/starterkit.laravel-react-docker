#!/bin/bash
set -e

# Run the DB clone script as testrunner
/bin/sh -c "/usr/local/bin/clone_db_for_testing.sh"

# Run php artisan test as www-data
/bin/sh -c ". /usr/local/bin/read-db-credentials-testing.sh; . /usr/local/bin/read-redis-password.sh; cd /var/www && php artisan test"