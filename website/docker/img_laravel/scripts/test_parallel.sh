#!/bin/bash
set -e

# Get filter parameter if provided
FILTER_ARG=""
if [ -n "$1" ]; then
    FILTER_ARG="--filter $1"
fi

# Run php artisan test as www-data
/bin/sh -c ". /usr/local/bin/read-db-credentials.sh && \
            . /usr/local/bin/read-redis-password.sh && \
            cd /var/www && \
            php artisan test --parallel -c phpunit.none.xml $FILTER_ARG"