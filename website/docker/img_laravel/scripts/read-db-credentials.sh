#!/bin/sh
set -e

# Source main app db credentials from Docker secrets
if [ -f /run/secrets/mysql_app_db_username ]; then
  DB_USERNAME=$(cat /run/secrets/mysql_app_db_username | tr -d '\r\n')
  export DB_USERNAME
fi
if [ -f /run/secrets/mysql_app_db_password ]; then
  DB_PASSWORD=$(cat /run/secrets/mysql_app_db_password | tr -d '\r\n')
  export DB_PASSWORD
fi
if [ -f /run/secrets/mysql_app_db_database ]; then
  DB_DATABASE=$(cat /run/secrets/mysql_app_db_database | tr -d '\r\n')
  export DB_DATABASE
fi
if [ -f /run/secrets/mysql_app_db_host ]; then
  DB_HOST=$(cat /run/secrets/mysql_app_db_host | tr -d '\r\n')
  export DB_HOST
fi
if [ -f /run/secrets/mysql_app_db_port ]; then
  DB_PORT=$(cat /run/secrets/mysql_app_db_port | tr -d '\r\n')
  export DB_PORT
fi