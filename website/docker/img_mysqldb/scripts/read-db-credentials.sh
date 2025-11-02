#!/bin/sh
set -e

# Source root password from Docker secret
if [ -f /run/secrets/mysql_db_rootpass ]; then
  MYSQL_ROOT_PASSWORD=$(cat /run/secrets/mysql_db_rootpass | tr -d '\r\n')
  export MYSQL_ROOT_PASSWORD
fi

# Source main app db credentials from split secrets
if [ -f /run/secrets/mysql_app_db_username ]; then
  MYSQL_USER=$(cat /run/secrets/mysql_app_db_username | tr -d '\r\n')
  export MYSQL_USER
fi
if [ -f /run/secrets/mysql_app_db_password ]; then
  MYSQL_PASSWORD=$(cat /run/secrets/mysql_app_db_password | tr -d '\r\n')
  export MYSQL_PASSWORD
fi
if [ -f /run/secrets/mysql_app_db_database ]; then
  MYSQL_DATABASE=$(cat /run/secrets/mysql_app_db_database | tr -d '\r\n')
  export MYSQL_DATABASE
fi

# Source testing db credentials from split secrets
if [ -f /run/secrets/mysql_testing_db_username ]; then
  MYSQL_USER_TESTING=$(cat /run/secrets/mysql_testing_db_username | tr -d '\r\n')
  export MYSQL_USER_TESTING
fi
if [ -f /run/secrets/mysql_testing_db_password ]; then
  MYSQL_PASSWORD_TESTING=$(cat /run/secrets/mysql_testing_db_password | tr -d '\r\n')
  export MYSQL_PASSWORD_TESTING
fi
if [ -f /run/secrets/mysql_testing_db_database ]; then
  MYSQL_DATABASE_TESTING=$(cat /run/secrets/mysql_testing_db_database | tr -d '\r\n')
  export MYSQL_DATABASE_TESTING
fi
