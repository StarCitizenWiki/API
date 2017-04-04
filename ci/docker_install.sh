#!/bin/bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && exit 0

set -xe

# Install git (the php image doesn't have it) which is required by composer
apt-get update -yqq
apt-get install git unzip libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng12-dev -yqq

# Install phpunit, the tool that we will use for testing
curl --location --output /usr/local/bin/phpunit https://phar.phpunit.de/phpunit.phar
chmod +x /usr/local/bin/phpunit

docker-php-ext-install gd
pecl install xdebug
docker-php-ext-enable xdebug