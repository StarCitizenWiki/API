# Star Citizen API

A Laravel-based API providing comprehensive access to Star Citizen game data, including vehicles, items, manufacturers, star systems, comm-links, and galactapedia entries. Features multi-language support (English, German, Chinese Simplified) with automated translation capabilities.

## Key Features

- **Complete Game Data**: Items, vehicles, manufacturers, tags, and specifications from unpacked game files
- **Starmap Integration**: Full star system, celestial object, and jump point data
- **Comm-Link Archive**: Automated downloading, importing, and translating of official RSI Comm-Links
- **Galactapedia**: Synchronized galactapedia articles with translation support
- **Ship Matrix & Pricing**: Daily updates of ship specifications, MSRP, and loaner information
- **Multi-language Support**: English, German, and Chinese Simplified translations
- **RESTful API**: Well-documented API endpoints for all data (see [docs.star-citizen.wiki](https://docs.star-citizen.wiki))

---

## Quick Start with Docker

### Prerequisites

- Docker 20.10+
- Docker Compose 2.0+
- Git

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/StarCitizenWiki/API.git
   cd API
   ```

2. **Initialize git submodules**
   ```bash
   git submodule update --init --recursive
   ```

3. **Configure environment variables**
   ```bash
   cp .env.example .env
   ```

   Edit `.env` and configure at minimum:
   - `APP_NAME`, `APP_KEY`, `APP_URL`
   - `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD`
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (should match POSTGRES_* values)

4. **Start all services**
   ```bash
   docker compose up -d
   ```

5. **Run initial setup**
   ```bash
   docker compose exec api php artisan key:generate
   docker compose exec api php artisan migrate
   docker compose exec api php artisan game:add-version --default <SC_UNPACKED_DATA_VERSION>
   docker compose exec api php artisan db:seed
   docker compose exec api php artisan game:sync
   ```

6. **Verify installation**

   Visit [http://localhost:8080](http://localhost:8080) to access the API.

---

## Environment Configuration

Configure your `.env` file with the following variables. Priority indicators help you understand which variables are critical.

**Priority Indicators:**
- 🔴 **Required** - Must be set for the application to function
- 🟡 **Important** - Should be configured for full functionality
- ⚪ **Optional** - Has sensible defaults, configure as needed

### Application Core

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `APP_NAME` | 🔴 | `Laravel` | Application name |
| `APP_KEY` | 🔴 | *(generate)* | Encryption key - generate with `php artisan key:generate` |
| `APP_ENV` | 🔴 | `local` | Environment: `local`, `production` |
| `APP_URL` | 🔴 | `http://localhost` | Base URL for the application |
| `APP_DEBUG` | 🔴 | `true` | Enable debug mode (set `false` in production) |
| `APP_LOCALE` | ⚪ | `en` | Default language |
| `APP_FALLBACK_LOCALE` | ⚪ | `en` | Fallback language |

### Database - PostgreSQL

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `DB_CONNECTION` | 🔴 | `pgsql` | Database driver (must be `pgsql`) |
| `DB_HOST` | 🔴 | `127.0.0.1` | Database host (`db` for Docker) |
| `DB_PORT` | 🔴 | `5432` | PostgreSQL port |
| `DB_DATABASE` | 🔴 | `api` | Database name |
| `DB_USERNAME` | 🔴 | `root` | Database username |
| `DB_PASSWORD` | 🔴 | *(empty)* | Database password |
| `POSTGRES_DB` | 🔴 | *(match DB_DATABASE)* | PostgreSQL container database name |
| `POSTGRES_USER` | 🔴 | *(match DB_USERNAME)* | PostgreSQL container username |
| `POSTGRES_PASSWORD` | 🔴 | *(match DB_PASSWORD)* | PostgreSQL container password |

### External Services

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `DEEPL_AUTH_KEY` | 🟡 | *(empty)* | DeepL API key for automated translations |

### Authentication

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `SANCTUM_STATEFUL_DOMAINS` | ⚪ | `localhost` | Comma-separated domains for stateful API authentication |
| `FORTIFY_ALLOW_REGISTRATION` | ⚪ | `true` | Allow user registration |

### Infrastructure

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `QUEUE_CONNECTION` | ⚪ | `database` | Queue driver: `sync`, `database`, `redis` |
| `CACHE_STORE` | ⚪ | `database` | Cache driver: `file`, `database`, `redis` |
| `SESSION_DRIVER` | ⚪ | `database` | Session storage: `file`, `cookie`, `database`, `redis` |
| `SESSION_LIFETIME` | ⚪ | `120` | Session lifetime in minutes |
| `REDIS_HOST` | ⚪ | `127.0.0.1` | Redis server host |
| `REDIS_PASSWORD` | ⚪ | `null` | Redis password |
| `REDIS_PORT` | ⚪ | `6379` | Redis port |

### Mail Configuration

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `MAIL_MAILER` | ⚪ | `log` | Mail driver: `smtp`, `sendmail`, `log` |
| `MAIL_HOST` | ⚪ | `127.0.0.1` | SMTP host |
| `MAIL_PORT` | ⚪ | `2525` | SMTP port |
| `MAIL_USERNAME` | ⚪ | `null` | SMTP username |
| `MAIL_PASSWORD` | ⚪ | `null` | SMTP password |
| `MAIL_FROM_ADDRESS` | ⚪ | `hello@example.com` | Default "from" email address |
| `MAIL_FROM_NAME` | ⚪ | `${APP_NAME}` | Default "from" name |

### Logging

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `LOG_CHANNEL` | ⚪ | `stack` | Logging channel: `stack`, `single`, `daily` |
| `LOG_LEVEL` | ⚪ | `debug` | Minimum log level: `debug`, `info`, `warning`, `error` |

### Filesystem

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `FILESYSTEM_DISK` | ⚪ | `local` | Default filesystem disk: `local`, `public`, `s3` |
| `AWS_ACCESS_KEY_ID` | ⚪ | *(empty)* | AWS access key (if using S3) |
| `AWS_SECRET_ACCESS_KEY` | ⚪ | *(empty)* | AWS secret key (if using S3) |
| `AWS_DEFAULT_REGION` | ⚪ | `us-east-1` | AWS region |
| `AWS_BUCKET` | ⚪ | *(empty)* | S3 bucket name |

### Data Migration - v2 to v3

⚪ **Only required when migrating from v2 to v3**

| Variable | Priority | Default | Description |
|----------|----------|---------|-------------|
| `MARIADB_HOST` | ⚪ | *(empty)* | MariaDB host for v2 migration |
| `MARIADB_DATABASE` | ⚪ | *(empty)* | MariaDB database name |
| `MARIADB_USERNAME` | ⚪ | *(empty)* | MariaDB username |
| `MARIADB_PASSWORD` | ⚪ | *(empty)* | MariaDB password |

---

## Custom Artisan Commands

The API provides specialized Artisan commands for managing game data, comm-links, and translations.

### User Management

| Command | Description | Common Options |
|---------|-------------|----------------|
| `user:add` | Create a new user | Interactive prompts for email, name, password |

### Comm-Link Management

| Command | Description | Common Options |
|---------|-------------|----------------|
| `comm-link:download` | Download Comm-Links for the given IDs | `<ids>` - Comma-separated list of Comm-Link IDs |
| `comm-link:import` | Import Comm-Link HTML from storage | Processes downloaded HTML files |
| `comm-link:schedule` | Download missing Comm-Links and queue imports | Automated by scheduler |
| `comm-link:download-new-versions` | Re-download and import existing Comm-Links | `--skip` - Skip certain operations |
| `comm-link:translate` | Translate all untranslated Comm-Links using DeepL | Requires `DEEPL_AUTH_KEY` |
| `comm-link:backfill-image-hashes` | Dispatch comm-link image hashing jobs | Processes images for deduplication |

### Game Data Management

| Command | Description | Common Options |
|---------|-------------|----------------|
| `game:add-version` | Add a new game version | `--default` - Set as default version |
| `game:sync` | Sync manufacturers, tags, and optionally import items/vehicles | Runs multiple import commands |
| `game:import-manufacturers` | Import game manufacturers from scunpacked data | Syncs manufacturer data |
| `game:import-tags` | Import game entity tags from scunpacked data | Syncs classification tags |
| `game:import-items` | Dispatch item import jobs for a specific game version | Imports weapons, components, etc. |
| `game:import-vehicles` | Dispatch vehicle import jobs for a specific game version | Imports ships and ground vehicles |
| `game:compute-item-base-ids` | Compute base_id values for item variants | Groups item variants |
| `game:backfill-shipmatrix-ids` | Backfill shipmatrix_id for game_vehicle_data records | Links vehicles to ship matrix |
| `game:review-vehicle-matches` | Interactively review and match unmatched game vehicles | Manual matching interface |

### Vehicle/Ship Matrix

| Command | Description | Common Options |
|---------|-------------|----------------|
| `vehicles:import-ship-matrix` | Download and import the latest ship matrix | Syncs official ship specifications |
| `vehicles:import-msrp` | Import all MSRPs from pledge store upgrade API | Updates ship pricing |
| `vehicles:import-loaner` | Import all loaner ship mappings | Updates loaner relationships |

### Starmap

| Command | Description | Common Options |
|---------|-------------|----------------|
| `starmap:sync` | Download and import the latest starmap | Syncs systems, objects, jump points |
| `starmap:translate-systems` | Translate all star systems using DeepL | Requires `DEEPL_AUTH_KEY` |

### Galactapedia

| Command | Description | Common Options |
|---------|-------------|----------------|
| `galactapedia:sync` | Sync galactapedia categories, articles, and properties | Downloads latest articles |
| `galactapedia:translate` | Translate all available galactapedia articles | Requires `DEEPL_AUTH_KEY` |

### Statistics

| Command | Description | Common Options |
|---------|-------------|----------------|
| `stats:sync` | Download and import funding statistics | Updates crowdfunding stats |

### Data Migration

| Command | Description | Common Options |
|---------|-------------|----------------|
| `data:migrate` | Migrate selected table groups from MariaDB to Postgres | `--all` - Migrate all tables |
| `data:migrate-translations` | Migrate translations from relational tables to JSON columns | Run after `data:migrate` |

---

## Docker Architecture

The application uses a multi-container Docker architecture with specialized services.

### Services Overview

| Service | Role | Description |
|---------|------|-------------|
| `api` | Web Server | Serves the Laravel application via PHP-FPM and Nginx |
| `scheduler` | Task Scheduler | Runs Laravel's scheduled tasks (cron replacement) |
| `queue` | Default Queue Worker | Processes jobs from the `default` queue |
| `queue_image_hashes` | Specialized Queue Worker | Processes `comm-link-hashes` queue jobs |
| `db` | Database | PostgreSQL 16 database server |

### Container Roles

The `CONTAINER_ROLE` environment variable determines what each container runs:

- **`app`** - Starts PHP-FPM and Nginx to serve web requests
- **`scheduler`** - Runs `php artisan schedule:work` to execute scheduled commands
- **`queue`** - Runs `php artisan queue:work` to process queued jobs
  - Customize with: `QUEUE_NAME`, `QUEUE_TRIES`, `QUEUE_MAX_JOBS`, `QUEUE_SLEEP`

### Queue Architecture

The application uses multiple queue workers for specialized workloads:

- **Default Queue** (`queue` service): Handles general background jobs (imports, syncs, translations)
- **Expensive Queue** (`expensive` service): Dedicated to expensive tasks like image hashing

### Volume Management

Persistent data is stored in mounted volumes:

- `./storage` - Laravel storage directory (logs, cache, uploaded files, API data submodules)
- `./var/lib/db` - PostgreSQL data directory

### Optional: Traefik Integration

For production deployments with automatic HTTPS, use the Traefik configuration:

```bash
docker compose -f compose.yaml -f compose.traefik.yaml up -d
```

This enables automatic SSL certificate management and reverse proxy capabilities.

---

## Scheduled Tasks

The following commands run automatically via Laravel's scheduler (handled by the `scheduler` service in Docker).

| Command | Schedule | Purpose |
|---------|----------|---------|
| `comm-link:schedule` | Every 15 minutes | Download missing Comm-Links and queue imports |
| `comm-link:download-new-versions` | Yearly | Re-download all existing Comm-Links |
| `stats:sync` | Daily at 20:00 | Update funding statistics |
| `vehicles:import-ship-matrix` | Daily | Sync ship matrix data |
| `vehicles:import-msrp` | Daily | Update ship pricing |
| `vehicles:import-loaner` | Daily | Update loaner mappings |
| `starmap:sync` | Monthly | Sync starmap data |
| `galactapedia:sync` | Daily at 2:00 | Sync galactapedia articles |
| `galactapedia:translate` | Daily at 3:00 | Translate galactapedia content |

All scheduled tasks are defined in `routes/console.php`.

---

## Migrating from v2

Ensure that both MariaDB and PostgreSQL are installed and running on your server.

MariaDB is configured using:

```dotenv
MARIADB_HOST=
MARIADB_DATABASE=
MARIADB_USERNAME=
MARIADB_PASSWORD=
```

Run these commands in order once. This will setup all migrations in postgres and migrate data from MariaDB to postgres.

```bash
php artisan db:wipe
php artisan migrate
php artisan game:add-version --default <SC_UNPACKED_DATA_VERSION>
php artisan data:migrate --all
php artisan db:seed
php artisan game:sync
php artisan starmap:sync

# Let the job runner finish processing all jobs
php artisan queue:work

# After ALL jobs have finished
php artisan data:migrate-translations
```

---

## Third-Party Projects & Licenses

This project integrates data and translations from the following external community projects:

### 1. StarCitizenDeutsch

**Repository:** https://github.com/rjcncpt/StarCitizen-Deutsch-INI

**License:** [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/) (Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International)

**Purpose:** Provides German language translations for Star Citizen game content, including items, vehicles, missions, and UI elements.

**Integration:** Integrated as a git submodule at `storage/app/api/StarCitizenDeutsch/`. German translations are loaded from `global.ini` files during the item import process via the Labels service.

**Attribution:** German translations are provided by the Star Citizen community translation team led by [rjcncpt](https://github.com/rjcncpt).

---

### 2. ScToolBoxLocales

**Repository:** https://github.com/StarCitizenToolBox/LocalizationData

**License:** License not explicitly stated in the repository

**Purpose:** Provides Chinese Simplified language translations for Star Citizen game content maintained by the StarCitizenToolBox community.

**Integration:** Located at `storage/app/api/ScToolBoxLocales/`. Chinese translations are loaded from `chinese_(simplified)/global.ini` during the item import process.

**Attribution:** Chinese translations are maintained by the StarCitizenToolBox organization and community contributors.

---

### 3. scunpacked-data

**Repository:** https://github.com/StarCitizenWiki/scunpacked-data

**License:** License not explicitly stated in the repository

**Purpose:** Contains unpacked and extracted Star Citizen game data in JSON format, including:
- Item definitions (`items.json`)
- Vehicle/ship data (`ships.json`)
- Manufacturer information (`manufacturers.json`)
- Translation labels (`labels.json`)
- Tags and classifications (`tags.json`)

**Integration:** Integrated as a separate repository at `storage/app/api/scunpacked-data/`. This is the primary data source for all game content imports, providing the raw game data that populates the API database.

**Attribution:** Data extraction and maintenance by the Star Citizen Wiki community.

---

## License Compliance

### StarCitizenDeutsch (CC BY-NC-SA 4.0)

This project uses German translations from StarCitizenDeutsch under the Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International license. This means:

- **Attribution (BY):** Credit must be given to the original creators
- **NonCommercial (NC):** The material cannot be used for commercial purposes
- **ShareAlike (SA):** Adaptations must be shared under the same license

**Required Attribution:**
German translations © Star Citizen Community Translation Team (rjcncpt and contributors)
Licensed under CC BY-NC-SA 4.0: https://creativecommons.org/licenses/by-nc-sa/4.0/

### Other Projects

ScToolBoxLocales and scunpacked-data do not have explicit license files in their repositories. These are community-maintained projects for the Star Citizen ecosystem. Users of this API should verify licensing requirements independently if using this data for commercial purposes.

---

## Acknowledgments

Special thanks to:
- The **Star Citizen German Translation Team** for their comprehensive German localization work
- The **StarCitizenToolBox** team for maintaining Chinese translations
- All community contributors who make these resources available

---

## Legal Notice

This project is a fan-made tool for the Star Citizen community and is not affiliated with or endorsed by Cloud Imperium Games or Roberts Space Industries. Star Citizen®, Roberts Space Industries®, and Cloud Imperium® are registered trademarks of Cloud Imperium Rights LLC and Cloud Imperium Rights Ltd.

All game data and translations are used for informational and educational purposes only.
