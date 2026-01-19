# Docker Official Image: nginx:alpine. Jan 14, 2026 at 7:51 pm
FROM nginx@sha256:b0f7830b6bfaa1258f45d94c240ab668ced1b3651c8a222aefe6683447c7bf55

COPY conf.d/*.template /etc/nginx/templates/

COPY conf.d/nginx.conf /etc/nginx/nginx.conf

# Copy entrypoint script into the image
COPY scripts/entrypoint.sh /usr/local/bin/entrypoint.sh

# Copy SSL-certificate polling script into image
COPY scripts/polling.sh /usr/local/bin/polling.sh

# Make sure it's executable
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/polling.sh

# Use the script as the entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["nginx", "-g", "daemon off;"]