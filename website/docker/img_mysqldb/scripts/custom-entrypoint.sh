#!/bin/sh
set -e

# Source main db credentials from Docker secret
. /read-db-credentials.sh

# Substitute variables in the SQL template
if [ -f /tmp/init-cloner.sql.template ]; then
  envsubst < /tmp/init-cloner.sql.template > /docker-entrypoint-initdb.d/init-cloner.sql
  rm /tmp/init-cloner.sql.template
fi

# Run the normal MySQL entrypoint
/usr/local/bin/docker-entrypoint.sh mysqld

# Remove the init-cloner.sql file to ensure sensitive data is unrecoverable
# rm -f /docker-entrypoint-initdb.d/init-cloner.sql

