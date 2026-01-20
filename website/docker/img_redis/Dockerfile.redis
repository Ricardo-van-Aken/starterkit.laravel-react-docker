# Docker Official Image: redis:alpine. Nov 20, 2025 at 9:07 pm
FROM redis@sha256:6cbef353e480a8a6e7f10ec545f13d7d3fa85a212cdcc5ffaf5a1c818b9d3798

# add a tiny wrapper that reads the secret file and calls the original entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]