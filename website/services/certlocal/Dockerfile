# Docker Official Image: alpine:latest. Dec 18, 2025 at 5:24 am
FROM alpine@sha256:865b95f46d98cf867a156fe4a135ad3fe50d2056aa3f25ed31662dff6da4eb62
RUN apk add --no-cache openssl

COPY scripts/generate_cert.sh /usr/local/bin/generate_cert.sh

RUN chmod +x /usr/local/bin/generate_cert.sh

CMD ["/usr/local/bin/generate_cert.sh"]