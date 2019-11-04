### Extensions
FROM php:7.3-apache as extensions

LABEL stage=intermediate

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libcurl4-gnutls-dev \
        libicu-dev \
        libmcrypt-dev \
        libvpx-dev \
        libxpm-dev \
        zlib1g-dev \
        libxml2-dev \
        libexpat1-dev \
        libbz2-dev \
        libgmp3-dev \
        libldap2-dev \
        unixodbc-dev \
        libpq-dev \
        libaspell-dev \
        libsnmp-dev \
        libpcre3-dev \
        libtidy-dev \
        libzip-dev

RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/

RUN docker-php-ext-install bcmath && \
    docker-php-ext-install ctype && \
    docker-php-ext-install curl && \
    docker-php-ext-install gmp && \
    docker-php-ext-install intl && \
    docker-php-ext-install json && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install opcache && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install simplexml && \
    docker-php-ext-install tokenizer && \
    docker-php-ext-install xml && \
    docker-php-ext-install zip

RUN echo '\
opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=16000\n\
opcache.validate_timestamps=0\n\
opcache.load_comments=Off\n\
opcache.save_comments=1\n\
opcache.fast_shutdown=0\n\
' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

### Composer
FROM php:7.3-apache as api

COPY --from=extensions /usr/local/etc/php/conf.d/docker-php-ext-bcmath.ini /usr/local/etc/php/conf.d/docker-php-ext-bcmath.ini
COPY --from=extensions /usr/local/etc/php/conf.d/docker-php-ext-intl.ini /usr/local/etc/php/conf.d/docker-php-ext-intl.ini
COPY --from=extensions /usr/local/lib/php/extensions/no-debug-non-zts-20180731/intl.so /usr/local/lib/php/extensions/no-debug-non-zts-20180731/intl.so
COPY --from=extensions /usr/local/lib/php/extensions/no-debug-non-zts-20180731/bcmath.so /usr/local/lib/php/extensions/no-debug-non-zts-20180731/bcmath.so

LABEL stage=intermediate

WORKDIR /api

COPY composer.json composer.lock /api/

# install git
RUN apt-get update && \
    apt-get install -y zip unzip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN /usr/bin/composer install --no-dev \
   --ignore-platform-reqs \
   --no-ansi \
   --no-autoloader \
   --no-interaction \
   --no-scripts

COPY / /api
RUN /usr/bin/composer dump-autoload --optimize --classmap-authoritative

### Final Image
FROM php:7.3-apache

COPY --from=api /api /opt/api
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY ./docker/start.sh /usr/local/bin/start

COPY --from=extensions /usr/local/etc/php/conf.d/*.ini /usr/local/etc/php/conf.d/
COPY --from=extensions /usr/local/lib/php/extensions/no-debug-non-zts-20180731/*.so /usr/local/lib/php/extensions/no-debug-non-zts-20180731/
COPY --from=extensions /usr/lib/x86_64-linux-gnu/libzip.so /usr/lib/x86_64-linux-gnu/libzip.so

VOLUME /opt/api/storage/logs
VOLUME /opt/api/storage/app

RUN chown -R www-data:www-data /opt/api && \
    chmod u+x /usr/local/bin/start && \
    chmod u+w /opt/api/storage && \
    chmod g+w /opt/api/storage && \
    a2enmod rewrite

CMD ["/usr/local/bin/start"]