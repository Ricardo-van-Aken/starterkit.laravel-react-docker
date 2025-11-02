#!/bin/sh
set -e

: "${APP_DOMAIN:=localhost}"

CERT_DIR="/etc/nginx/ssl/live/$APP_DOMAIN"
mkdir -p "$CERT_DIR"

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout "$CERT_DIR/privkey.pem" \
  -out "$CERT_DIR/fullchain.pem" \
  -subj "/CN=LocalDockerCert" 2>/dev/null

echo "Self-signed certificate generated for CN=$APP_DOMAIN at $CERT_DIR" 