# This file contains commands that are used to start processes in the docker containers, such as migrating the
# database, or running tests. Make sure the corresponding container is running before executing any of these commands.

# This file is a cheat sheet - commands are not actually ran.
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

# Run tests in parallel (doesnt overwrite existing db, since parallel tests run in seperate DBs. But no cache isolation)
# NOTE: We have this command to run tests in ci/cd, with a clean build. For testing on running systems this command
#       is not recommended, as tests use the same cache as the running application.
docker exec -u www-data laravel_app sh -c "bash /usr/local/bin/test.sh --parallel -c phpunit.none.xml"

# Run tests on a single thread (overwrites the existing db, no cache isolation)
docker exec -u www-data laravel_app sh -c "bash /usr/local/bin/test.sh -c phpunit.none.xml"

###
## Seperate Test environment
###

# Run tests on a single thread (verifies Database SSL certificates)
docker exec -u testrunner laravel_app sh -c 'bash /usr/local/bin/test_cloned_db.sh'

# Run tests on a single thread (allows self-signed Database SSL certificates for local dev)
docker exec -u testrunner laravel_app sh -c 'bash /usr/local/bin/test_cloned_db.sh --allow-self-signed-ssl'

# Run tests in parallel (verifies Database SSL certificates)
docker exec -u testrunner laravel_app sh -c 'bash /usr/local/bin/test_cloned_db.sh --parallel'

# Run tests in parallel (allows self-signed Database SSL certificates for local dev)
docker exec -u testrunner laravel_app sh -c 'bash /usr/local/bin/test_cloned_db.sh --parallel --allow-self-signed-ssl'

###
## No DB Required
###

# Run tests without infra
docker exec -u www-data laravel_app sh -c 'bash /usr/local/bin/test.sh -c phpunit.xml'

# Run static analysis within docker
docker exec -u www-data laravel_app sh -c 'cd /var/www && composer analyse'

