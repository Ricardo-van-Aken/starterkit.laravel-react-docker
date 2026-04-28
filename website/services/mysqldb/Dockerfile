# Docker Official Image: mysql:8.0-debian. Jan 13, 2026 at 5:03 am
FROM mysql@sha256:707f5c087531fde2fe4cd0fa3a125c7467e99b7f6282a3e6ed338604aedb8c25

# Remove MySQL repository immediately to avoid GPG signature errors on apt-get update.
# (The built-in keys often expire in official images, but we only need standard Debian packages here anyway).
RUN rm -f /etc/apt/sources.list.d/mysql.list

RUN --mount=type=cache,id=apt-cache,target=/var/cache/apt \
    --mount=type=cache,id=apt-lib,target=/var/lib/apt \
    apt-get update && \
    apt-get install -y openssl libssl-dev gettext-base && \
    rm -rf /var/lib/apt/lists/*

# Sql entrypoint
COPY scripts/entry.sql.template /tmp/entry.sql.template

# Scripts
COPY scripts/custom-entrypoint.sh /custom-entrypoint.sh
COPY scripts/generate-certs.sh /generate-certs.sh
COPY scripts/read-db-credentials.sh /read-db-credentials.sh
RUN chmod u+x /custom-entrypoint.sh /read-db-credentials.sh /generate-certs.sh
