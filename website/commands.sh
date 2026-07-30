#!/usr/bin/env bash
# This file contains commands that are used to start processes in the docker containers, such as migrating the
# database, or running tests. Make sure the corresponding container is running before executing any of these commands.

# This file is a cheat sheet - commands are not actually ran.
# shellcheck disable=SC2317
exit 0

###
## Standard environment
###

# Run migrations
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan migrate'

# Seed the default data (such as roles and permissions)
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan db:seed --class=RequiredDataSeeder'

# Seed fake data
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan db:seed --class=FakeDataSeeder'

# Run tests on a single thread against the default (non-testing) db (overwrites the existing db, no cache isolation).
# Note: this cannot use ./test.sh, which always sources the testing db credentials. It runs as www-data and
# sources the normal db credentials so the suite hits the default application database.
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-app-key.sh; . /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; cd /var/www && php artisan test -c phpunit.none.xml'

###
## Seperate Test environment (isolated test database)
###
## Use ./test.sh, which forwards any flags to the container's test.sh.

# Run tests on a single thread (verifies Database SSL certificates)
./test.sh

# Run tests on a single thread (allows self-signed Database SSL certificates for local dev)
./test.sh --allow-self-signed-ssl

# Run tests in parallel (verifies Database SSL certificates)
./test.sh --parallel

# Run tests in parallel (allows self-signed Database SSL certificates for local dev)
./test.sh --parallel --allow-self-signed-ssl

###
## No DB Required
###

# Run tests without infra (in-memory SQLite, no DB/Redis containers needed)
./test.sh -c phpunit.xml

# Run static analysis within docker
docker exec -u www-data laravel_app sh -c 'cd /var/www && composer analyse'

