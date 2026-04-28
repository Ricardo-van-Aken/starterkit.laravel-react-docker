#!/bin/sh
set -e

# Source testing db credentials from Docker secrets
if [ -f /run/secrets/mysql_testing_db_username ]; then
  DB_USERNAME_TESTING=$(cat /run/secrets/mysql_testing_db_username | tr -d '\r\n')
  export DB_USERNAME_TESTING
fi
if [ -f /run/secrets/mysql_testing_db_password ]; then
  DB_PASSWORD_TESTING=$(cat /run/secrets/mysql_testing_db_password | tr -d '\r\n')
  export DB_PASSWORD_TESTING
fi
if [ -f /run/secrets/mysql_testing_db_database ]; then
  DB_DATABASE_TESTING=$(cat /run/secrets/mysql_testing_db_database | tr -d '\r\n')
  export DB_DATABASE_TESTING
fi
if [ -f /run/secrets/mysql_app_db_host ]; then
  DB_HOST=$(cat /run/secrets/mysql_app_db_host | tr -d '\r\n')
  export DB_HOST
fi
if [ -f /run/secrets/mysql_app_db_port ]; then
  DB_PORT=$(cat /run/secrets/mysql_app_db_port | tr -d '\r\n')
  export DB_PORT
fi
