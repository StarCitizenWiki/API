<p align="center">
<img src="https://star-citizen.wiki/thumb.php?f=Star_Citizen_Wiki_Logo.png&width=300" alt="Star Citizen Wiki Logo" />
</p>
<p align="center">
    <img src="https://img.shields.io/github/actions/workflow/status/StarCitizenWiki/API/docker-dev.yml" />
    <a href="https://hub.docker.com/r/scwiki/api" alt="Docker Hub">
        <img src="https://img.shields.io/docker/pulls/scwiki/api" />
    </a>
</p>

# Star Citizen Wiki API

The star-citizen.wiki API. Automatically scrapes Comm-Links, Stats and the Ship-Matrix.

## Documentation
The documentation can be found at https://docs.star-citizen.wiki.

## Installation
Using docker and docker-compose

Pull `scwiki/api` or build the image:
```shell script
$ ./docker-build.sh
```

Create the folders:
```shell script
$ mkdir -p ./var/lib/api.star-citizen.wiki/storage
$ mkdir -p ./var/lib/api.star-citizen.wiki/logs
$ mkdir -p ./var/lib/api.star-citizen.wiki/db
$ touch ./var/lib/api.star-citizen.wiki/db/db.sqlite
$ mkdir -p ./etc/api.star-citizen.wiki
```

Create the production environment file:  
Paste content into `./etc/api.star-citizen.wiki/env-production`.
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

# Translated text to use for wiki comm-link pages Default de_DE
WIKI_TRANS_LOCALE=

# DEEPL Access key for automated Comm-Link translations
DEEPL_AUTH_KEY=
# Target language of DeepL Translations Default: DE
DEEPL_TARGET_LOCALE=

SC_DATA_VERSION=3.13

```

## Simple Docker-Compose
```yaml
version: '3'

services:
  api.star-citizen.wiki:
    image: scwiki/api:dev
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
    image: scwiki/api:dev
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
    image: scwiki/api:dev
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

Alternatively you can set `ADMIN_AUTH_USER_STUB=true` in your environment file to disable OAuth-authorization.  
Be warned, that this skips the authorization check completely and logs everyone in with an admin account.

## Commands
The API exposes several commands callable inside the container with `php artisan COMMAND`.  
All available commands can be listed by calling `php artisan`.

To get help for a specific command call `php artisan help COMMAND`.

### Importing _all_ data
To import all and everything you can run:
```shell
php artisan data:populate
```

This command will import _everything_ but will take multiple hours to days.  
You can alternatively skip some parts using the following options:
- `--skipCommLinks`
- `--skipGalactapedia`
- `--skipStarmap`
- `--skipScUnpacked`

### Comm-Links
All Comm-Link commands require that the database is seeded using `php artisan db:seed`!

#### Import missing Comm-Links
Inside the container execute
```shell script
php artisan comm-links:schedule
```
This will download all Comm-Links, parse them and create metadata.  
The import command can be safely stopped by `Ctrl+C`.

Comm-Links should be accessible through `API_URL/api/comm-links`.

#### Import downloaded Comm-Links  
Downloaded Comm-Links can be imported by calling `php artisan comm-links:import --all`.  
This command will dispatch parsing jobs for every downloaded Comm-Link found in `storage/app/comm_links`.

#### Download specific Comm-Links
Command: `php artisan comm-links:download ID1 ID2 ID3 ...`.  
Passing the `--import` flag will import the Comm-Links after downloading them.  
Passing `--overwrite` will force the import even if a Comm-Link file exists locally. 

#### Download Comm-Link images
Command: `php artisan comm-links:download-images`.  
This command downloads all parsed Comm-Link images and saves them locally to `storage/app/public/comm_link_images`.  
Jobs are dispatched on the `comm_link_images` queue. This queue needs to be called manually inside the container using:  
`php artisan queue:work --queue=comm_link_images`.

#### Create Comm-Link Image metadata
Command: `php artisan comm-links:images-create-metadata`.  
Requests the following metadata for each image: `size`, `mime_type`, `last_modified`.

Without the image metadata no images will be shown on the image index view.

#### Create Comm-Link Image hashes
Command: `php artisan comm-links:images-create-hashes`.  
Creates image hashes used by the reverse image search. Requires present metadata.

### Ship-Matrix
#### Downloading
```shell script
php artisan ship-matrix:download --import
```

Ships should be accessible through `API_URL/api/ships`

### Stats
#### Downloading
```shell script
php artisan stats:download --import
```

#### Importing pre-downloaded stats
The API includes stat dumps from `2012-11-13` until `2018-03-25`. To import the dumps call `php artisan db:seed --class StatTableSeeder`.  

Stats should be accessible through `API_URL/api/stats`

### Galactapedia
#### Import
```shell script
php artisan galactapedia:import-categories
php artisan galactapedia:import-articles
php artisan galactapedia:import-properties
```

Articles should be accessible through `API_URL/api/galactapedia`

### In-Game Items / Vehicles
To import the data found in the scunpacked folder run:
```shell
php artisan sc:import-items
```

### Schedule
The schedule container specified in `docker-compose` will run the following commands:

- Download current funding statistics daily at 20:00:00
- Import missing Comm-Links every hour
- Download all missing Comm-Links starting at the first each month
- Import the Ship-Matrix twice a day
- Import all available Galactapedia articles daily at 02:00
  - Requires to run `galactapedia:import-categories` first

## Remarks
The API is heavily integrated into the german Star Citizen Wiki.  
There is currently no known other installation except api.star-citizen.wiki

You'll have basic API access if you register an account on star-citizen.wiki. Each user gets an API token with almost unlimited rate-limiting.

If you have any questions open an issue on this repository or contact foxftw@star-citizen.wiki 
