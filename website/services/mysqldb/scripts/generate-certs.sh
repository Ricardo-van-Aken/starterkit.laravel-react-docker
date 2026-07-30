#!/bin/sh
set -e

CERT_DIR="/mysql-certs"
CONTAINER_NAME="mysql_db"

# Only generate certs once
if [ ! -f "$CERT_DIR/ca.pem" ]; then
  echo "Generating MySQL SSL certificates..."

  mkdir -p "$CERT_DIR"

  # CA
  openssl genrsa 4096 > "$CERT_DIR/ca-key.pem"
  openssl req -new -x509 -nodes -days 3650 \
    -key "$CERT_DIR/ca-key.pem" \
    -out "$CERT_DIR/ca.pem" \
    -subj "/CN=MySQL-CA"

  # Server key
  openssl genrsa 4096 > "$CERT_DIR/server-key.pem"

  # Server cert with a SubjectAltName for the "mysql_db" hostname, required for strict TLS
  # verification (MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=true) to succeed.
  printf 'subjectAltName=DNS:%s\n' "$CONTAINER_NAME" > "$CERT_DIR/server-ext.cnf"

  openssl req -new \
    -key "$CERT_DIR/server-key.pem" \
    -out "$CERT_DIR/server-req.pem" \
    -subj "/CN=$CONTAINER_NAME"

  openssl x509 -req -days 3650 \
    -in "$CERT_DIR/server-req.pem" \
    -CA "$CERT_DIR/ca.pem" \
    -CAkey "$CERT_DIR/ca-key.pem" \
    -set_serial 01 \
    -extfile "$CERT_DIR/server-ext.cnf" \
    -out "$CERT_DIR/server-cert.pem"

  rm -f "$CERT_DIR/server-ext.cnf"

  chmod 600 "$CERT_DIR"/*-key.pem
fi