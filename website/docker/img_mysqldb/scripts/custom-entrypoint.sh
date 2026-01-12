#!/bin/sh
set -e

# Source main db credentials from Docker secret
. /read-db-credentials.sh

# Substitute variables in the SQL template
if [ -f /tmp/init-cloner.sql.template ]; then
  envsubst < /tmp/init-cloner.sql.template > /docker-entrypoint-initdb.d/init-cloner.sql
  rm /tmp/init-cloner.sql.template
fi

/entrypoint-generate-certs.sh

chown -R mysql:mysql /mysql-certs

chmod 600 /mysql-certs/server-key.pem
chmod 644 /mysql-certs/server-cert.pem
chmod 644 /mysql-certs/ca.pem
chown -R mysql:mysql /mysql-certs

# Run the normal MySQL entrypoint
/usr/local/bin/docker-entrypoint.sh mysqld \
  --ssl-ca=/mysql-certs/ca.pem \
  --ssl-cert=/mysql-certs/server-cert.pem \
  --ssl-key=/mysql-certs/server-key.pem

# Remove the init-cloner.sql file to ensure sensitive data is unrecoverable
# rm -f /docker-entrypoint-initdb.d/init-cloner.sql

