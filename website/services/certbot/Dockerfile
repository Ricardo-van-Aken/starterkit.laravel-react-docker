# certbot/certbot:latest. Dec 10, 2025 at 7:01 pm
FROM certbot/certbot@sha256:5255405f241cd64b121f36ef0172711420816bbbd9c029674de53e5ed953182d

# Copy your renewal script
COPY scripts/certbot_renew.sh /certbot_renew.sh
RUN chmod +x /certbot_renew.sh

# Add cron job: run every day at 2am
RUN echo "0 2 * * * root /certbot_renew.sh >> /var/log/cron.log 2>&1" > /etc/crontabs/certbot-renew

# Give execution rights on the cron job
RUN chmod 0644 /etc/crontabs/certbot-renew

# Apply cron job
RUN crontab /etc/crontabs/certbot-renew

COPY scripts/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start cron in the foreground
CMD ["crond", "&&", "tail", "-f", "/var/log/cron.log"]