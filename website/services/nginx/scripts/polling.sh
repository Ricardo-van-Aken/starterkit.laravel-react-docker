#!/bin/sh
set -e

command="$(basename -- "$0"):"

cmd_echo() {
    echo "$command $1"
}

CERT_DIR="/etc/letsencrypt/live/$APP_DOMAIN"
SSL_DIR="/etc/nginx/conf.d"

# Keep checking until the certificate directory exists
while true; do
    cmd_echo "Looking for certificates in ${CERT_DIR}..."

    if [ -d $CERT_DIR ]; then
        cmd_echo "Found certificates in ${CERT_DIR}..."

        cmd_echo "Waiting for the ssl configuration file to appear in ${SSL_DIR}..."

        while [ ! -f "${SSL_DIR}/ssl.conf.tmp" ]; do
            sleep 2
        done

        cmd_echo "Found ssl configuration file, reloading..."

        # Rename the nginx SSL configuration template to activate SSL configuration
        mv "${SSL_DIR}/ssl.conf.tmp" "${SSL_DIR}/ssl.conf"

        # Verify that nginx syntax config remains correct
        nginx -t

        # Reload nginx to apply the new SSL configuration
        nginx -s reload

        break
    fi

    sleep 5
done

cmd_echo "Loaded nginx SSL configuration files!"