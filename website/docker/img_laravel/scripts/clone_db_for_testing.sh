#!/bin/bash

# Source testing user DB credentials from Docker secrets
. /usr/local/bin/read-db-credentials-testing.sh

# Also read the main database name for cloning
if [ -f /run/secrets/mysql_app_db_database ]; then
  DB_DATABASE=$(cat /run/secrets/mysql_app_db_database | tr -d '\r\n')
fi

# Check if the user passed the flag to allow self-signed certificates
SSL_ARGS="--ssl"
if [ "$1" = "--allow-self-signed-ssl" ]; then
  SSL_ARGS="--ssl --ssl-verify-server-cert=false"
fi

# Drop and recreate the test database
mysql $SSL_ARGS --protocol=TCP -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME_TESTING" -p"$DB_PASSWORD_TESTING" -e "DROP DATABASE IF EXISTS \`$DB_DATABASE_TESTING\`;"
mysql $SSL_ARGS --protocol=TCP -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME_TESTING" -p"$DB_PASSWORD_TESTING" -e "CREATE DATABASE \`$DB_DATABASE_TESTING\`;"

# Dump only the main database schema (and optionally data) and import into test database
mysqldump --no-tablespaces $SSL_ARGS --protocol=TCP -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME_TESTING" -p"$DB_PASSWORD_TESTING" "$DB_DATABASE" | \
  mysql $SSL_ARGS --protocol=TCP -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME_TESTING" -p"$DB_PASSWORD_TESTING" "$DB_DATABASE_TESTING"
