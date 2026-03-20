# This file contains commands that are used to start processes in the docker containers, such as migrating the
# database, or running tests. Make sure the corresponding container is running before executing any of these commands.

# This file is a cheat sheet - commands are not actually ran.
exit 0

###
## Standard DB
###

# Run migrations
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan migrate'

# Seed the default data (such as roles and permissions)
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan db:seed --class=RequiredDataSeeder'

# Seed fake data
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-db-credentials.sh; . /usr/local/bin/read-redis-password.sh; php artisan db:seed --class=FakeDataSeeder'

# Run tests in parallel (doesnt overwrite existing db):
docker exec -u www-data laravel_app sh -c ". /usr/local/bin/read-db-credentials.sh && . /usr/local/bin/read-redis-password.sh && cd /var/www && php artisan test --parallel -c phpunit.none.xml"

###
## Seperate Test DB
###

# Run tests on a single thread (verifies standard SSL certificates)
docker exec -u testrunner laravel_app sh -c '. /usr/local/bin/clone_and_test.sh'

# Run tests on a single thread (allows self-signed SSL certificates for local dev)
docker exec -u testrunner laravel_app sh -c '. /usr/local/bin/clone_and_test.sh --allow-self-signed-ssl'

###
## No DB Required
###

# Run static analysis within docker
docker exec -u www-data laravel_app sh -c 'cd /var/www && composer analyse'

