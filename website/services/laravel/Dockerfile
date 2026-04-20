ARG PHP_VERSION=8.3
ARG SCHEDULER_BASE=volume-prod

#################################################
###                                           ###
###   Stage: Runtime Base (Minimal)           ###
###                                           ###
#################################################
# Docker Official Image. php:8.3-fpm-alpine - Apr 16, 2026 at 11:56 am
FROM php@sha256:9158b5d619387f3aeb903281228edfce08cab963e1591158532cf0271d3e61cc AS runtime-base

# Install runtime system dependencies
RUN --mount=type=cache,id=apk-cache,target=/var/cache/apk \
    apk add \
    libpng \
    libjpeg-turbo \
    mariadb-client \
    shadow \
    bash

# Install PHP extensions
RUN --mount=type=cache,id=apk-cache,target=/var/cache/apk \
    apk add --virtual .build-deps \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql bcmath gd \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps

# --- Users and Groups --- #
ARG WWW_UID=82
ARG WWW_GID=82

# Set group ID and user ID for www-data user. This user runs the laravel application
RUN usermod -u ${WWW_UID} www-data && groupmod -g ${WWW_GID} www-data

# Add a testrunner user and group for running the db cloning script
RUN addgroup -g 1002 testrunner && \
    adduser -u 1002 -D -S -G testrunner -s /bin/bash testrunner

# Create shared group for files/scripts used by both www-data user and testrunner user
RUN addgroup -g 1003 shared && \
    addgroup www-data shared && \
    addgroup testrunner shared

WORKDIR /var/www

# --- Scripts for loading in Docker secrets and cloning/testing the laravel database --- #
ARG DOCKERFILE_DIR=.

# Copy scripts
COPY ${DOCKERFILE_DIR}/scripts/*.sh /usr/local/bin/

# Set permissions
RUN chown root:www-data         /usr/local/bin/read-db-credentials.sh           && chmod 550 /usr/local/bin/read-db-credentials.sh && \
    chown root:www-data         /usr/local/bin/test.sh                          && chmod 550 /usr/local/bin/test.sh && \
    chown root:shared           /usr/local/bin/read-redis-password.sh           && chmod 550 /usr/local/bin/read-redis-password.sh && \
    chown root:shared           /usr/local/bin/read-db-credentials-testing.sh   && chmod 550 /usr/local/bin/read-db-credentials-testing.sh && \
    chown root:testrunner       /usr/local/bin/test_cloned_db.sh                && chmod 550 /usr/local/bin/test_cloned_db.sh && \
    chown root:testrunner       /usr/local/bin/clone_db_for_testing.sh          && chmod 550 /usr/local/bin/clone_db_for_testing.sh


#################################################
###                                           ###
###   build-tools                             ###
###                                           ###
#################################################
# Fast tool-rich base for local development with mounting of host system files. Doesnt do any building of
# assets in the image build.
FROM runtime-base AS build-tools

# Install node/npm, git, unzip, and composer for local development and runtime tasks
RUN --mount=type=cache,id=apk-cache,target=/var/cache/apk \
    apk add \
    git \
    unzip \
    nodejs \
    npm

# Docker Official Image. composer:2.9.7 - Apr 17, 2026 at 8:13 pm
COPY --from=composer@sha256:dc292c5c0f95f526b051d4c341bf08e7e2b18504c74625e3203d7f123050e318 /usr/bin/composer /usr/bin/composer


#################################################
###                                           ###
###   Target: bind-mount                      ###
###                                           ###
#################################################
# Fast tool-rich base for local development with mounting of host system files. Doesnt do any building of
# assets in the image build.
FROM build-tools AS bind-mount

USER www-data
EXPOSE 9000


#################################################
###                                           ###
###   Backend: Composer Dependencies          ###
###                                           ###
#################################################
# Since composer checks if system dependencies for the packages are met during installation, we use our app
# image base as the base image. This ensures the final app image has the same installed dependencies that
# composer checks for.
FROM build-tools AS backend-deps-base
WORKDIR /app

FROM backend-deps-base AS backend-deps-prod
COPY laravel_root/composer.json laravel_root/composer.lock ./
# We dont run scripts, because these depend on application files which are not present in this stage.
RUN --mount=type=cache,id=composer-cache,target=/root/.composer/cache \
    composer install --no-dev --no-scripts

FROM backend-deps-base AS backend-deps-dev
COPY laravel_root/composer.json laravel_root/composer.lock ./
# We dont run scripts, because these depend on application files which are not present in this stage.
RUN --mount=type=cache,id=composer-cache,target=/root/.composer/cache \
    composer install --no-scripts


#################################################
###                                           ###
###   Frontend: NPM Dependencies              ###
###                                           ###
#################################################
# Docker Official Image. node:25-alpine - Apr 16, 2026 at 1:40 am
FROM build-tools AS frontend-deps
WORKDIR /app
COPY laravel_root/package.json laravel_root/package-lock.json ./
RUN --mount=type=cache,id=npm-cache,target=/root/.npm npm ci

#################################################
###                                           ###
###   Full-Stack Builder                      ###
###                                           ###
#################################################
# Unified builder for Vite assets and Composer optimization
FROM build-tools AS builder-base
WORKDIR /var/www

FROM builder-base AS builder-prod
# Import pre-downloaded dependencies
COPY --from=backend-deps-prod /app/vendor ./vendor
COPY --from=frontend-deps /app/node_modules ./node_modules
# Copy full application context for compilation (Wayfinder needs the app context and vendor)
COPY laravel_root/ .
RUN --mount=type=cache,id=vite-cache,target=/var/www/node_modules/.vite \
    npx vite build
# Assembly & Optimization
RUN composer dump-autoload --optimize

FROM builder-base AS builder-dev
# Import pre-downloaded dependencies
COPY --from=backend-deps-dev /app/vendor ./vendor
COPY --from=frontend-deps /app/node_modules ./node_modules
# Copy full application context for compilation (Wayfinder needs the app context and vendor)
COPY laravel_root/ .
RUN --mount=type=cache,id=vite-cache,target=/var/www/node_modules/.vite \
    npx vite build
# Assembly & Optimization
RUN composer dump-autoload --optimize


#################################################
###                                           ###
###   Target: volume-dev                      ###
###                                           ###
#################################################
# Clean runtime identical to production
FROM runtime-base AS volume-dev
# Copy runtime-essential application files from dev builder (includes tests, NO node_modules)
COPY --chown=www-data:www-data --from=builder-dev /var/www/app ./app
COPY --chown=www-data:www-data --from=builder-dev /var/www/bootstrap ./bootstrap
COPY --chown=www-data:www-data --from=builder-dev /var/www/config ./config
COPY --chown=www-data:www-data --from=builder-dev /var/www/database ./database
COPY --chown=www-data:www-data --from=builder-dev /var/www/lang ./lang
COPY --chown=www-data:www-data --from=builder-dev /var/www/public ./public
COPY --chown=www-data:www-data --from=builder-dev /var/www/resources ./resources
COPY --chown=www-data:www-data --from=builder-dev /var/www/routes ./routes
COPY --chown=www-data:www-data --from=builder-dev /var/www/storage ./storage
COPY --chown=www-data:www-data --from=builder-dev /var/www/tests ./tests
COPY --chown=www-data:www-data --from=builder-dev /var/www/vendor ./vendor
COPY --chown=www-data:www-data --from=builder-dev /var/www/artisan .
COPY --chown=www-data:www-data --from=builder-dev /var/www/composer.json .
COPY --chown=www-data:www-data --from=builder-dev /var/www/composer.lock .
COPY --chown=www-data:www-data --from=builder-dev /var/www/phpstan.neon .
COPY --chown=www-data:www-data --from=builder-dev /var/www/phpunit*.xml .

USER www-data
EXPOSE 9000


#################################################
###                                           ###
###   Target: volume-prod                     ###
###                                           ###
#################################################
# Minimal production-ready runtime
FROM runtime-base AS volume-prod
# Copy runtime-essential application files from prod builder (NO tests, NO node_modules)
COPY --chown=www-data:www-data --from=builder-prod /var/www/app ./app
COPY --chown=www-data:www-data --from=builder-prod /var/www/bootstrap ./bootstrap
COPY --chown=www-data:www-data --from=builder-prod /var/www/config ./config
COPY --chown=www-data:www-data --from=builder-prod /var/www/database ./database
COPY --chown=www-data:www-data --from=builder-prod /var/www/lang ./lang
COPY --chown=www-data:www-data --from=builder-prod /var/www/public ./public
COPY --chown=www-data:www-data --from=builder-prod /var/www/resources ./resources
COPY --chown=www-data:www-data --from=builder-prod /var/www/routes ./routes
COPY --chown=www-data:www-data --from=builder-prod /var/www/storage ./storage
COPY --chown=www-data:www-data --from=builder-prod /var/www/vendor ./vendor
COPY --chown=www-data:www-data --from=builder-prod /var/www/artisan .
COPY --chown=www-data:www-data --from=builder-prod /var/www/composer.json .
COPY --chown=www-data:www-data --from=builder-prod /var/www/composer.lock .

USER www-data
EXPOSE 9000


#################################################
###                                           ###
###   Target: scheduler                       ###
###                                           ###
#################################################
FROM ${SCHEDULER_BASE} AS scheduler
USER root

# Define crontab for the www-data user
RUN echo "* * * * * . /usr/local/bin/read-db-credentials.sh && . /usr/local/bin/read-redis-password.sh && php /var/www/artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/www-data
RUN chmod 0600 /etc/crontabs/www-data

# Run crond in foreground
CMD ["crond", "-f", "-L", "/dev/stdout"]
