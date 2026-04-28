#!/bin/sh

set -e

check_certs() {
    if [ ! -f "/etc/letsencrypt/live/$APP_DOMAIN/fullchain.pem" ]; then
        return 1
    fi
    return 0
}

if [ "$1" != "init" ] && ! check_certs; then
    echo "=============================================="
    echo "No SSL certificates found for $APP_DOMAIN!"
    echo "Checked for /etc/letsencrypt/live/$APP_DOMAIN/fullchain.pem"
    echo ""
    echo "To generate your first certificates, run:"
    echo "docker compose run --rm certbot init"
    echo ""
    echo "For staging/test certificates:"
    echo "docker compose run --rm certbot init --staging"
    echo "=============================================="

    # Exit process
    exit 1
fi

# Handle custom init command
if [ "$1" = "init" ]; then
    shift
    STAGING_FLAG=""
    if [ "$1" = "--staging" ]; then
        STAGING_FLAG="--staging"
        shift
    fi

    # Delete existing certificates
    if check_certs; then
        echo "Existing certificates were found for: $APP_DOMAIN"

        NON_INTERACTIVE=0

        for arg in "$@"; do
            if [ "$arg" = "--non-interactive" ]; then
                NON_INTERACTIVE=1
            fi
        done

        # Only delete certs in interactive mode
        if [ "$NON_INTERACTIVE" -eq 0 ]; then
            while true; do
                read -r -p "Delete certificate for $APP_DOMAIN? [yY/nN]: " ans

                ans=$(printf '%s' "$ans" | tr '[:upper:]' '[:lower:]')

                case "$ans" in
                    y) certbot delete --cert-name "$APP_DOMAIN" --non-interactive; break ;;
                    n|"") exit 0 ;;
                    *) echo "Please answer yY/nN." ;;
                esac
            done
        fi
    fi

    echo ""
    
    echo "Generating (new) initial certificates for: $APP_DOMAIN"

    exec certbot certonly $STAGING_FLAG --webroot -w /var/www/certbot \
        $(echo "$APP_DOMAIN" | tr ',' '\n' | xargs -I {} echo "-d {}") \
        --cert-name "$APP_DOMAIN" \
        --email "$DOMAIN_EMAIL" \
        --agree-tos \
        --no-eff-email \
        --non-interactive \
        "$@"
fi

# Run any other commands
exec "$@"