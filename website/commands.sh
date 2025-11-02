# This file contains commands that are used to start processes in the docker containers, such as migrating the
# database, or running tests. Make sure the corresponding container is running before executing any of these commands.

# This file is a cheat sheet - commands are not actually ran.
exit 0

# To run migrations on the standard db, read the db credentials and migrate as 'www-data' user.
docker exec -u www-data laravel_app sh -c '. /usr/local/bin/read-db-credentials.sh; php artisan migrate'

# To run tests on a clone of the db, run the clone_and_test script as 'testrunner' user.
docker exec -u testrunner laravel_app sh -c '. /usr/local/bin/clone_and_test.sh'
