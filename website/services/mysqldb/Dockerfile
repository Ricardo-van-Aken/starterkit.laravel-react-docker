# Docker Official Image: mysql:9.7 (LTS, Oracle Linux 9). Multi-arch (amd64 + arm64).
FROM mysql@sha256:ad88e1c86cbf12ef52d0a0360cd4b774d22956c5ee9c565645fb019d7aacb6c3

# The 9.x images are Oracle Linux based, so install packages with microdnf.
# openssl: the CLI used by generate-certs.sh. gettext: provides envsubst for the SQL template.
RUN --mount=type=cache,id=dnf-cache,target=/var/cache/dnf \
    microdnf install -y openssl gettext && \
    microdnf clean all

# Sql entrypoint
COPY scripts/entry.sql.template /tmp/entry.sql.template

# Scripts
COPY scripts/custom-entrypoint.sh /custom-entrypoint.sh
COPY scripts/generate-certs.sh /generate-certs.sh
COPY scripts/read-db-credentials.sh /read-db-credentials.sh
RUN chmod u+x /custom-entrypoint.sh /read-db-credentials.sh /generate-certs.sh
