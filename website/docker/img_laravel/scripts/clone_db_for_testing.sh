#!/bin/bash

DB_HOST=db
DB_PORT=3306

# Source testing user DB credentials from Docker secrets
. /usr/local/bin/read-db-credentials-testing.sh

# Get the main DB name (testing user has read access to this DB)
if [ -f /run/secrets/mysql_app_db_database ]; then
  DB_DATABASE=$(cat /run/secrets/mysql_app_db_database | tr -d '\r\n')
  export DB_DATABASE
fi

# Drop and recreate the test database
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME_TESTING" -p"$DB_PASSWORD_TESTING" -e "DROP DATABASE IF EXISTS \`$DB_DATABASE_TESTING\`;"
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME_TESTING" -p"$DB_PASSWORD_TESTING" -e "CREATE DATABASE \`$DB_DATABASE_TESTING\`;"

# Dump only the main database schema (and optionally data) and import into test database
mysqldump --no-tablespaces -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME_TESTING" -p"$DB_PASSWORD_TESTING" "$DB_DATABASE" | \
  mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME_TESTING" -p"$DB_PASSWORD_TESTING" "$DB_DATABASE_TESTING"
