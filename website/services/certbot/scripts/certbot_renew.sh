#!/bin/bash

# Log file location inside the container
LOGFILE=/var/log/cronjobs.log

# Run certbot renew and log the result
{
    echo "$(date) Cronjob for updating HTTPS Certificate";
    certbot renew && \
        echo -e "\nJOB SUCCESS" || \
        echo -e "\nJOB FAILED";
    # Attempt to reload nginx if possible (requires docker socket mount)
    # if command -v docker >/dev/null 2>&1; then
    #     docker exec nginx nginx -s reload && echo "Nginx reloaded" || echo "Failed to reload Nginx";
    # else
    #     echo "Docker not available, cannot reload Nginx automatically.";
    # fi
    echo -e '------------------------------------------------------------\n\n';
} >> "$LOGFILE" 2>&1
