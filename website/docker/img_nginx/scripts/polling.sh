#!/bin/sh
set -e

command="$(basename -- "$0"):"

cmd_echo() {
    echo "$command $1"
}

# Keep checking until the certificate directory exists
while true; do
    if [ -d /etc/letsencrypt/live/${APP_DOMAIN} ]; then

        # Wait for the ssl configuration file to appear
        while [ ! -f /etc/nginx/conf.d/ssl.conf.tmp ]; do
            sleep 2
        done

        # Rename the nginx SSL configuration template to activate SSL configuration
        mv "/etc/nginx/conf.d/ssl.conf.tmp" "/etc/nginx/conf.d/ssl.conf"

        # Verify that nginx syntax config remains correct
        nginx -t

        # Reload nginx to apply the new SSL configuration
        nginx -s reload

        break
    fi

    sleep 10
done

cmd_echo "Loaded nginx SSL configuration files!"