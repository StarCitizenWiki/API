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
$ mkdir -p /var/lib/api.star-citizen.wiki/storage
$ mkdir -p /var/lib/api.star-citizen.wiki/logs
$ mkdir -p /var/lib/api.star-citizen.wiki/db
$ mkdir -p /etc/api.star-citizen.wiki
```

Create the production environment file:  
Paste content into `/etc/api.star-citizen.wiki/env-production`.
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=

LOG_LEVEL=info

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

QUEUE_DRIVER=database

DB_HOST=db
DB_DATABASE=CHANGE_ME
DB_USERNAME=CHANGE_ME
DB_PASSWORD=CHANGE_ME

MAIL_DRIVER=smtp # OR log
MAIL_HOST=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=
MAIL_USERNAME=
MAIL_PASSWORD=

WIKI_URL=https://star-citizen.wiki
WIKI_API_URL=https://star-citizen.wiki/api.php

# MediaWiki Bot credentials for accessind the MW APi
WIKI_OAUTH_ID=
WIKI_OAUTH_SECRET=

THROTTLE_GUEST_REQUESTS=60

APP_URL=https://api.star-citizen.wiki

# For Creating automated comm-link pages on the wiki
# Requires the OAuth extension activated
WIKI_TRANS_OAUTH_CONSUMER_TOKEN=
WIKI_TRANS_OAUTH_CONSUMER_SECRET=

WIKI_TRANS_OAUTH_ACCESS_TOKEN=
WIKI_TRANS_OAUTH_ACCESS_SECRET=

# DEEPL Access key for automated comm link translations
DEEPL_AUTH_KEY=

# User account on RSI // Login currently BROKEN due to recaptcha
RSI_USERNAME=
RSI_PASSWORD=

```

```yaml
version: '3'

services:
  api.star-citizen.wiki:
    image: scw-api:7.0
    container_name: api.star-citizen.wiki
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    depends_on:
      - db
    links:
      - db
    expose:
      - 80
    environment:
      APP_ENV: production
      CONTAINER_ROLE: app
    volumes:
      - /var/lib/api.star-citizen.wiki/storage:/opt/api/storage/app
      - /var/lib/api.star-citizen.wiki/logs:/opt/api/storage/logs
      - /etc/api.star-citizen.wiki/env-production:/opt/api/.env
      - /etc/localtime:/etc/localtime:ro

  db:
    image: mariadb:latest
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    volumes:
      - /var/lib/api.star-citizen.wiki/db:/var/lib/mysql
      - /etc/localtime:/etc/localtime:ro
    environment:
      MYSQL_DATABASE: Use values form production env
      MYSQL_ROOT_PASSWORD: Change
      MYSQL_USER: Use values form production env
      MYSQL_PASSWORD: Use values form production env
    networks:
      - internal

  scheduler:
    image: scw-api:7.0
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    depends_on:
      - api.star-citizen.wiki
      - db
    volumes:
      - /var/lib/api.star-citizen.wiki/storage:/opt/api/storage/app
      - /var/lib/api.star-citizen.wiki/logs:/opt/api/storage/logs
      - /etc/api.star-citizen.wiki/env-production:/opt/api/.env
      - /etc/localtime:/etc/localtime:ro
    environment:
      APP_ENV: production
      CONTAINER_ROLE: scheduler
    networks:
      - internal

  queue:
    image: scw-api:7.0
    restart: unless-stopped
    security_opt:
      - no-new-privileges:true
    depends_on:
      - api.star-citizen.wiki
      - db
    volumes:
      - /var/lib/api.star-citizen.wiki/storage:/opt/api/storage/app
      - /var/lib/api.star-citizen.wiki/logs:/opt/api/storage/logs
      - /etc/api.star-citizen.wiki/env-production:/opt/api/.env
      - /etc/localtime:/etc/localtime:ro
    environment:
      APP_ENV: production
      CONTAINER_ROLE: queue
    networks:
      - internal

networks:
  api.star-citizen.wiki:
    external: true
  internal:
```

Start the container:
```shell script
docker-compose up -d
```

Log into the container and run the setup scripts:
```shell script
$ docker exec -it api.star-citizen.wiki /bin/bash
# In the Container
$ cd /opt/api
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
php artisan download:comm-link-versions
```

Comm-Links should be accessible through `API_URL/api/comm-links`.

### Import the Ship-Matrix
```shell script
php artisan import:shipmatrix --d
```

Ships should be accessible through `API_URL/api/ships`

### Schedule
The schedule container specified in `docker-compose` will run the following commands:

- Download current fund statistics at 20:00:00
- Import missing Comm-Links each hour
- Download all missing Comm-Links starting at the first each month
- Import the Ship-Matrix twice a day

## Remarks
The API is heavily integrated into the german Star Citizen Wiki.  
There is currently no known other installation except api.star-citizen.wiki

You'll have basic API access if you register an account on star-citizen.wiki. Each user gets an API token with almost unlimited rate-limiting.

If you have any questions open an issue on this repository or contact foxftw@star-citizen.wiki 