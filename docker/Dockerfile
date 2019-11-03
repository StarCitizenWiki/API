FROM php:7.3-apache as intermediate

LABEL stage=intermediate

WORKDIR /API

# install git
RUN apt-get update && \
    apt-get install -y git ssh zlib1g-dev libicu-dev g++ zip unzip

# add credentials on build
ARG SSH_PRIVATE_KEY=/path/to/.ssh/key

# Set up root user SSH access for GitHub
ADD ${SSH_PRIVATE_KEY} /root/.ssh/id_rsa

RUN chmod 600 /root/.ssh/id_rsa && \
    touch /root/.ssh/known_hosts && \
    ssh-keyscan tea.octofox.de >> /root/.ssh/known_hosts && \
    git clone git@tea.octofox.de:StarCitizenWiki/API.git --branch develop .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install bcmath && \
    docker-php-ext-install intl && \
    /usr/bin/composer install --no-dev && \
    rm -f /root/.ssh/id*

FROM php:7.3-apache

COPY --from=intermediate /API /opt/api
COPY --from=intermediate /API/docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY --from=intermediate /API/docker/start.sh /usr/local/bin/start

VOLUME /opt/api/storage/logs
VOLUME /opt/api/storage/app

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        #git \
        #zip \
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
    docker-php-ext-install intl && \
    docker-php-ext-install ctype && \
    docker-php-ext-install zip && \
    docker-php-ext-install json && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install tokenizer && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install curl && \
    docker-php-ext-install gmp && \
    docker-php-ext-install opcache && \
    docker-php-ext-install simplexml && \
    docker-php-ext-install xml

#RUN chown -R www-data:www-data /var/www/html \
RUN chown -R www-data:www-data /opt/api && \
    chmod u+x /usr/local/bin/start && \
    a2enmod rewrite

CMD ["/usr/local/bin/start"]