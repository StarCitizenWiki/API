#!/usr/bin/env bash

set -e

IP=/var/www/html

/usr/local/bin/php "$IP/artisan" schedule:run --verbose --no-interaction >> /dev/null 2>&1

exit 0
