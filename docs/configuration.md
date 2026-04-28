# Environment Configuration

Full reference for all environment variables. Configure these in your `.env` file.

**Priority indicators:**

- 🔴 **Required** — Must be set for the application to function
- 🟡 **Important** — Should be configured for full functionality
- ⚪ **Optional** — Has sensible defaults, configure as needed

## Application Core

| Variable     | Priority | Default            | Description                                               |
|--------------|----------|--------------------|-----------------------------------------------------------|
| `APP_NAME`   | 🔴       | `Laravel`          | Application name                                          |
| `APP_KEY`    | 🔴       | *(generate)*       | Encryption key — generate with `php artisan key:generate` |
| `APP_ENV`    | 🔴       | `local`            | Environment: `local`, `production`                        |
| `APP_URL`    | 🔴       | `http://localhost` | Base URL for the application                              |
| `APP_DEBUG`  | 🔴       | `true`             | Enable debug mode (set `false` in production)             |
| `APP_LOCALE` | ⚪        | `en`               | Default language                                          |

## Database — PostgreSQL

| Variable            | Priority | Default                 | Description                        |
|---------------------|----------|-------------------------|------------------------------------|
| `DB_CONNECTION`     | 🔴       | `pgsql`                 | Database driver (must be `pgsql`)  |
| `DB_HOST`           | 🔴       | `127.0.0.1`             | Database host (`db` for Docker)    |
| `DB_PORT`           | 🔴       | `5432`                  | PostgreSQL port                    |
| `DB_DATABASE`       | 🔴       | `api`                   | Database name                      |
| `DB_USERNAME`       | 🔴       | `root`                  | Database username                  |
| `DB_PASSWORD`       | 🔴       | *(empty)*               | Database password                  |
| `POSTGRES_DB`       | 🔴       | *(match `DB_DATABASE`)* | PostgreSQL container database name |
| `POSTGRES_USER`     | 🔴       | *(match `DB_USERNAME`)* | PostgreSQL container username      |
| `POSTGRES_PASSWORD` | 🔴       | *(match `DB_PASSWORD`)* | PostgreSQL container password      |

## External Services

| Variable                                 | Priority | Default                                | Description                                                                                                                                   |
|------------------------------------------|----------|----------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------|
| `DEEPL_AUTH_KEY`                         | 🟡       | *(empty)*                              | DeepL API key for automated translations                                                                                                      |
| `DEEPL_TARGET_LOCALE`                    | ⚪        | `de`                                   | Locale sent to DeepL for automated translations                                                                                               |
| `DEEPL_TRANSLATION_LOCALE`               | ⚪        | *(derived from `DEEPL_TARGET_LOCALE`)* | Translation key used when saving automated translations. Override when the DeepL locale differs from the app locale key (e.g. `de_DE` → `de`) |
| `COMM_LINKS_AUTO_TRANSLATE_AFTER_IMPORT` | ⚪        | `false`                                | Automatically translate Comm-Links after import                                                                                               |
| `RSI_URL`                                | ⚪        | `https://robertsspaceindustries.com`   | Base URL for Roberts Space Industries website                                                                                                 |
| `UEXCORP_API_URL`                        | ⚪        | `https://api.uexcorp.uk/2.0`           | UEX Corp API endpoint for item & vehicle prices                                                                                               |
| `IMAGES_TOOLS_API_URL`                   | ⚪        | `https://starcitizen.tools/api.php`    | MediaWiki API for starcitizen.tools image sources                                                                                             |
| `IMAGES_SCW_API_URL`                     | ⚪        | `https://star-citizen.wiki/api.php`    | MediaWiki API for star-citizen.wiki image sources                                                                                             |
| `IMAGES_CSTONE_BASE_URL`                 | ⚪        | `https://cstone.space/uifimages/`      | Base URL for cstone.space direct image sources                                                                                                |

## Authentication

| Variable                     | Priority | Default  | Description              |
|------------------------------|----------|----------|--------------------------|
| `FORTIFY_ALLOW_REGISTRATION` | ⚪        | `false`  | Allow user registration  |

## Analytics

| Variable                    | Priority | Default   | Description                                    |
|-----------------------------|----------|-----------|------------------------------------------------|
| `PLAUSIBLE_ENABLED`         | ⚪        | `false`   | Enable Plausible analytics tracking            |
| `PLAUSIBLE_DOMAIN`          | ⚪        | *(empty)* | Domain to track (e.g. `api.star-citizen.wiki`) |
| `PLAUSIBLE_TRACKING_SCRIPT` | ⚪        | *(empty)* | Full URL to the Plausible tracking script      |

## Logging

| Variable                   | Priority | Default  | Description                                            |
|----------------------------|----------|----------|--------------------------------------------------------|
| `LOG_CHANNEL`              | ⚪        | `stack`  | Logging channel: `stack`, `single`, `daily`            |
| `LOG_STACK`                | ⚪        | `single` | Channels included in the stack logger                  |
| `LOG_DEPRECATIONS_CHANNEL` | ⚪        | `null`   | Channel for deprecation warnings                       |
| `LOG_LEVEL`                | ⚪        | `debug`  | Minimum log level: `debug`, `info`, `warning`, `error` |

## Data Migration — v2 to v3

⚪ Only required when migrating from v2 to v3. See the [v2 migration guide](migration-v2.md).

| Variable                  | Priority | Default   | Description                              |
|---------------------------|----------|-----------|------------------------------------------|
| `MIGRATE_FROM_CONNECTION` | ⚪        | `mariadb` | Source database connection for migration |
| `MIGRATE_TO_CONNECTION`   | ⚪        | `pgsql`   | Target database connection for migration |
| `MARIADB_HOST`            | ⚪        | *(empty)* | MariaDB host for v2 migration            |
| `MARIADB_DATABASE`        | ⚪        | *(empty)* | MariaDB database name                    |
| `MARIADB_USERNAME`        | ⚪        | *(empty)* | MariaDB username                         |
| `MARIADB_PASSWORD`        | ⚪        | *(empty)* | MariaDB password                         |
