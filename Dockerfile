### Stage 1: build PHP extensions on PHP 8.4
FROM php:8.4-apache AS extensions

LABEL stage=intermediate

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libicu-dev \
        zlib1g-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        libgmp-dev \
        libpq-dev \
        libxml2-dev \
        libonig-dev; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    docker-php-ext-install -j"$(nproc)" bcmath gmp intl opcache pdo_mysql zip

RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" gd

RUN set -eux; \
    { \
      echo 'opcache.enable=1'; \
      echo 'opcache.memory_consumption=256'; \
      echo 'opcache.interned_strings_buffer=16'; \
      echo 'opcache.max_accelerated_files=16000'; \
      echo 'opcache.validate_timestamps=0'; \
      echo 'opcache.load_comments=Off'; \
      echo 'opcache.save_comments=1'; \
      echo 'opcache.fast_shutdown=0'; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

### Stage 2: composer install on PHP 8.4
FROM php:8.4-apache AS api

LABEL stage=intermediate
WORKDIR /api

COPY --from=extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends zip unzip git; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --chown=www-data:www-data composer.json composer.lock /api/
USER www-data

RUN set -eux; \
    composer install --no-dev --ignore-platform-reqs --no-ansi --no-autoloader --no-interaction --no-scripts

COPY --chown=www-data:www-data / /api

RUN rm -rf storage/app/api/scunpacked-data storage/app/api/ScToolBoxLocales

RUN composer dump-autoload --optimize --classmap-authoritative

### Stage 3: final runtime image on PHP 8.4
FROM php:8.4-apache

USER root
WORKDIR /var/www/html

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ffmpeg \
        libfreetype6 \
        libjpeg62-turbo \
        libwebp7 \
        libpng16-16 \
        libzip4; \
    rm -rf /var/lib/apt/lists/*

COPY --chown=www-data:www-data --from=api /api /var/www/html
COPY --from=extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/

COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --chown=www-data:www-data --chmod=770 ./docker/start.sh /usr/local/bin/start
COPY --chown=www-data:www-data --chmod=770 ./docker/schedule.sh /usr/local/bin/schedule

RUN set -eux; \
    echo 'memory_limit = 1G' > /usr/local/etc/php/conf.d/docker-php-memlimit.ini; \
    echo 'max_execution_time = 60' > /usr/local/etc/php/conf.d/docker-php-executiontime.ini; \
    a2enmod rewrite

USER www-data

RUN set -eux; \
    php artisan storage:link; \
    php artisan optimize

CMD ["/usr/local/bin/start"]
