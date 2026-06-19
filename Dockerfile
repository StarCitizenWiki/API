# Stage 0: base with PHP extensions (runtime-safe)
FROM php:8.5-apache AS base
WORKDIR /var/www/html

# helper to install extensions + dependencies correctly
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/install-php-extensions

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ffmpeg \
        libicu-dev \
        libpq-dev \
        libzip-dev; \
    rm -rf /var/lib/apt/lists/*; \
    \
    install-php-extensions \
        bcmath \
        gmp \
        intl \
        pdo_pgsql \
        pdo_mysql \
        redis \
        zip \
        gd \
        mbstring \
        curl \
        dom \
        xml; \
    \
    a2enmod rewrite; \
    sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# OPcache is built-in on PHP 8.5; just configure it.
RUN set -eux; \
    { \
      echo 'opcache.enable=1'; \
      echo 'opcache.memory_consumption=256'; \
      echo 'opcache.interned_strings_buffer=16'; \
      echo 'opcache.max_accelerated_files=16000'; \
      echo 'opcache.validate_timestamps=0'; \
      echo 'opcache.save_comments=1'; \
    } > /usr/local/etc/php/conf.d/docker-opcache.ini; \
    echo 'memory_limit = 1G' > /usr/local/etc/php/conf.d/docker-php-memlimit.ini; \
    echo 'max_execution_time = 60' > /usr/local/etc/php/conf.d/docker-php-executiontime.ini

COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./docker/apache-prefork.conf /etc/apache2/conf-available/prefork-tuning.conf
COPY ./docker/apache-keepalive.conf /etc/apache2/conf-available/keepalive-tuning.conf
RUN a2enconf prefork-tuning keepalive-tuning


# Stage 1: composer deps
FROM base AS vendor

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip zip; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Make composer cache writable for www-data (optional but avoids warnings)
ENV COMPOSER_HOME=/tmp/composer

COPY --chown=www-data:www-data composer.json composer.lock /var/www/html/

USER www-data
RUN set -eux; \
    composer install \
      --no-dev \
      --no-ansi \
      --no-interaction \
      --no-progress \
      --prefer-dist \
      --no-scripts

# Now bring in the full app (includes artisan)
COPY --chown=www-data:www-data . /var/www/html

# Run Laravel composer scripts now that artisan exists, then optimize autoload
RUN set -eux; \
    composer run-script post-autoload-dump; \
    composer dump-autoload --optimize --classmap-authoritative


# Stage 2: frontend build (Vite)
FROM node:22-alpine AS frontend
WORKDIR /var/www/html

# Install deps first for better caching
COPY package.json package-lock.json* /var/www/html/
RUN set -eux; \
    if [ -f package-lock.json ]; then npm ci; else npm install; fi

# Copy sources needed for the build and build assets
COPY . /var/www/html
RUN set -eux; \
    npm run build


# Stage 3: final runtime
FROM base AS app
WORKDIR /var/www/html

COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html

# Copy built Vite assets into the runtime image
COPY --from=frontend --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build

COPY --chown=www-data:www-data --chmod=770 ./docker/start.sh /usr/local/bin/start

RUN php artisan storage:link

USER www-data
CMD ["/usr/local/bin/start"]
