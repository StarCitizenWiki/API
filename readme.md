<p align="center">
<img src="https://star-citizen.wiki/images/thumb/e/ef/Star_Citizen_Wiki_Logo.png/157px-Star_Citizen_Wiki_Logo.png?794c2" alt="Star Citizen Wiki Logo" />
</p>

# Star Citizen Wiki API

The star-citizen.wiki API. Automatically scrapes Comm-Links, Stats and the Ship-Matrix.

## Installation
Using docker and docker-compose

Build the image:
```shell script
$ ./docker-build.sh
```

Create the folders:
```shell script
$ mkdir -p var/lib/api.star-citizen.wiki/storage
$ mkdir -p var/lib/api.star-citizen.wiki/logs
$ mkdir -p var/lib/api.star-citizen.wiki/db
$ touch var/lib/api.star-citizen.wiki/db/db.sqlite
$ mkdir -p etc/api.star-citizen.wiki
# 33:33 is the user and group id of dockers www-data user 
$ chown -R 33:33 var/lib/api.star-citizen.wiki
$ chown -R 33:33 etc/api.star-citizen.wiki
```

Create the production environment file:  
Paste content into `/etc/api.star-citizen.wiki/env-production`.
```env
APP_URL=http://localhost
APP_ENV=local
APP_DEBUG=false
APP_KEY=

LOG_LEVEL=info

# Run Jobs through the queue container
QUEUE_DRIVER=database

# Set to true to skip MediaWiki OAuth and always login with an admin account
# DO NOT USE IN PRODUCTION
ADMIN_AUTH_USE_STUB=true

# SQLITE
DB_CONNECTION=sqlite
DB_DATABASE=db.sqlite

# MYSQL
# DB_HOST=db
# DB_DATABASE=CHANGE_ME
# DB_USERNAME=CHANGE_ME
# DB_PASSWORD=CHANGE_ME

MAIL_DRIVER=smtp # OR log
MAIL_HOST=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=
MAIL_USERNAME=
MAIL_PASSWORD=

# Max 10.000 Requests in one Second
THROTTLE_GUEST_REQUESTS=10000
THROTTLE_PERIOD=1

#WIKI_URL=https://star-citizen.wiki
#WIKI_API_URL=https://star-citizen.wiki/api.php

# MediaWiki Bot credentials for accessing the MW API
WIKI_OAUTH_ID=
WIKI_OAUTH_SECRET=

# For Creating automated Comm-Link pages on the wiki
# Requires an the OAuth extension and an active app 
WIKI_TRANS_OAUTH_CONSUMER_TOKEN=
WIKI_TRANS_OAUTH_CONSUMER_SECRET=

WIKI_TRANS_OAUTH_ACCESS_TOKEN=
WIKI_TRANS_OAUTH_ACCESS_SECRET=

# DEEPL Access key for automated Comm-Link translations
DEEPL_AUTH_KEY=

# User account on RSI // Login currently BROKEN due to recaptcha
RSI_USERNAME=
RSI_PASSWORD=

```

## Simple Docker-Compose
```yaml
version: '3'

services:
  api.star-citizen.wiki:
    image: scw-api:7.0
    container_name: api.star-citizen.wiki
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    ports:
      - 127.0.0.1:8000:80
    environment:
      CONTAINER_ROLE: app
    volumes:
      - ./var/lib/api.star-citizen.wiki/storage:/var/www/html/storage/app
      - ./var/lib/api.star-citizen.wiki/logs:/var/www/html/storage/logs
      - ./var/lib/api.star-citizen.wiki/db/db.sqlite:/var/www/html/database/db.sqlite
      - ./etc/api.star-citizen.wiki/env-production:/var/www/html/.env
      - /etc/localtime:/etc/localtime:ro

  scheduler:
    image: scw-api:7.0
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    depends_on:
      - api.star-citizen.wiki
    volumes:
      - ./var/lib/api.star-citizen.wiki/storage:/var/www/html/storage/app
      - ./var/lib/api.star-citizen.wiki/logs:/var/www/html/storage/logs
      - ./var/lib/api.star-citizen.wiki/db/db.sqlite:/var/www/html/database/db.sqlite
      - ./etc/api.star-citizen.wiki/env-production:/var/www/html/.env
      - /etc/localtime:/etc/localtime:ro
    environment:
      CONTAINER_ROLE: scheduler

  queue:
    image: scw-api:7.0
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    depends_on:
      - api.star-citizen.wiki
    volumes:
      - ./var/lib/api.star-citizen.wiki/storage:/var/www/html/storage/app
      - ./var/lib/api.star-citizen.wiki/logs:/var/www/html/storage/logs
      - ./var/lib/api.star-citizen.wiki/db/db.sqlite:/var/www/html/database/db.sqlite
      - ./etc/api.star-citizen.wiki/env-production:/var/www/html/.env
      - /etc/localtime:/etc/localtime:ro
    environment:
      CONTAINER_ROLE: queue

```

Start the container:
```shell script
docker-compose up -d
```

Log into the container and run the setup scripts:
```shell script
$ docker exec -it api.star-citizen.wiki /bin/bash
# In the Container
$ php artisan key:generate
$ php artisan migrate # Confirm
$ php artisan db:seed # Confirm

```

## Usage
Users are authorized via OAuth through your wiki. 
You'll need to have the [Extension:OAuth](https://www.mediawiki.org/wiki/Extension:OAuth) installed and activated.  
Register an oauth app on the wiki, and set the consumer and access tokens in the production environment file.  
Users with the administrator group set in the wiki will have admin access on the api.

### Import Comm-Links
Inside the container execute
```shell script
php artisan comm-links:import-missing
```
This will download all Comm-Links, parse them and create metadata.  
The import command can be safely stopped by `Ctrl+C`.  
Downloaded Comm-Links can be imported by calling `php artisan comm-links:import`.

Comm-Links should be accessible through `API_URL/api/comm-links`.

### Import the Ship-Matrix
```shell script
php artisan ship-matrix:download --import
```

Ships should be accessible through `API_URL/api/ships`

### Schedule
The schedule container specified in `docker-compose` will run the following commands:

- Download current funding statistics daily at 20:00:00
- Import missing Comm-Links every hour
- Download all missing Comm-Links starting at the first each month
- Import the Ship-Matrix twice a day

## Remarks
The API is heavily integrated into the german Star Citizen Wiki.  
There is currently no known other installation except api.star-citizen.wiki

You'll have basic API access if you register an account on star-citizen.wiki. Each user gets an API token with almost unlimited rate-limiting.

If you have any questions open an issue on this repository or contact foxftw@star-citizen.wiki 