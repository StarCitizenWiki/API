# Artisan Commands & Scheduled Tasks

## User Management

| Command      | Description         | Options                                         |
|--------------|---------------------|-------------------------------------------------|
| `user:add`   | Create a new user   | Interactive prompts for email, name, password   |


## Comm-Link Management

| Command                               | Description                                                       | Options                                                                        |
|---------------------------------------|-------------------------------------------------------------------|--------------------------------------------------------------------------------|
| `comm-link:download`                  | Download Comm-Links for the given IDs                             | `<ids>` — Comma-separated list of Comm-Link IDs                                |
| `comm-link:import`                    | Import Comm-Link HTML from storage                                | Processes downloaded HTML files                                                |
| `comm-link:schedule`                  | Download missing Comm-Links and queue imports                     | Automated by scheduler                                                         |
| `comm-link:download-new-versions`     | Re-download and import Comm-Links from the database               | `--skip` — Skip certain operations                                             |
| `comm-link:translate`                 | Translate all untranslated Comm-Links using DeepL                 | Requires `DEEPL_AUTH_KEY`                                                      |
| `comm-link:backfill-image-hashes`     | Dispatch comm-link image hashing jobs                             | Processes images for deduplication                                             |
| `comm-link:backfill-counts`           | Backfill comm-link images_count and links_count from pivot tables | `--chunk` — Process per chunk size, `--dry-run` — Preview changes              |
| `comm-link:compute-similar-image-ids` | Compute and mark similar/duplicate comm-link images               | `--queue` — Queue name (default: `expensive`), `--recent` — Only recent images |

## Game Data Management

| Command                          | Description                                                                                                       | Options                                                                                                                                                                                         |
|----------------------------------|-------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `game:add-version`               | Add a new game version                                                                                            | `<code>` — Version in `Major.Minor.Patch.SCOPE.Buildnumber` format, `--default` — Set as default, `--released-at` — Release date/time                                                           |
| `game:sync-data`                 | Sync game labels, manufacturers, entity tags, resource types, and optional game data imports (alias: `game:sync`) | `--game-version`, `--skip-items`, `--skip-vehicles`, `--skip-starmap`, `--skip-resources`, `--skip-compute-item-groups`, `--skip-backfill-shipmatrix-ids`, `--skip-factions`, `--skip-missions` |
| `game:import-manufacturers`      | Import game manufacturers from scunpacked data                                                                    | Syncs manufacturer data                                                                                                                                                                         |
| `game:import-tags`               | Import game entity tags from scunpacked data                                                                      | Syncs classification tags                                                                                                                                                                       |
| `game:import-labels`             | Import translation labels from JSON and INI files into database                                                   |                                                                                                                                                                                                 |
| `game:import-items`              | Dispatch item import jobs for a specific game version                                                             | `<version>` — Game version code                                                                                                                                                                 |
| `game:import-vehicles`           | Dispatch vehicle import jobs for a specific game version                                                          | `<version>` — Game version code                                                                                                                                                                 |
| `game:import-blueprints`         | Import game blueprints for a specific game version                                                                | `<version>`, `--path` — Relative path to blueprints JSON (default: `blueprints.json`)                                                                                                           |
| `game:import-commodities`        | Import game commodities from scunpacked data                                                                      | `--path` — Relative path to commodities JSON                                                                                                                                                    |
| `game:import-factions`           | Import game factions from scunpacked data                                                                         |                                                                                                                                                                                                 |
| `game:import-missions`           | Import missions for a specific game version using queued jobs                                                     | `<version>` — Game version code                                                                                                                                                                 |
| `game:import-images`             | Import images from wiki sources for items, vehicles, starmap locations, and commodities                           | `--chunk` — Number of items per enrichment job (default: `500`)                                                                                                                                 |
| `game:import-item-prices`        | Import item & vehicle prices from UEX Corp API for the default game version                                       | `--chunk` — Number of UUIDs per enrichment job (default: `50`)                                                                                                                                  |
| `game:import-resources`          | Import game resources for a specific game version                                                                 | `<version>`, `--path` — Relative path to resources JSON                                                                                                                                         |
| `game:import-resource-locations` | Import game resource locations for a specific game version                                                        | `<version>`, `--path` — Relative path to locations JSON                                                                                                                                         |
| `game:import-resource-data`      | Import commodities, resources, and resource locations for a specific game version                                 | `<version>` — Game version code                                                                                                                                                                 |
| `game:import-starmap`            | Import starmap data for a specific game version                                                                   | `<version>` — Game version code                                                                                                                                                                 |
| `game:compute-item-groups`       | Compute variant groups and set items for a game version                                                           | `--game-version` — Specific version code (defaults to current default)                                                                                                                          |
| `game:backfill-shipmatrix-ids`   | Backfill shipmatrix_id for game_vehicle_data records                                                              | `--game-version`, `--dry-run`, `--limit`                                                                                                                                                        |
| `game:review-vehicle-matches`    | Interactively review and match unmatched game vehicles                                                            | `--limit` — Number of vehicles to review (default: `20`)                                                                                                                                        |

## Vehicle / Ship Matrix

| Command                       | Description                                    | Options                            |
|-------------------------------|------------------------------------------------|------------------------------------|
| `vehicles:import-ship-matrix` | Download and import the latest ship matrix     | Syncs official ship specifications |
| `vehicles:import-msrp`        | Import all MSRPs from pledge store upgrade API | Updates ship pricing               |
| `vehicles:import-loaner`      | Import all loaner ship mappings                | Updates loaner relationships       |

## Starmap

| Command                     | Description                            | Options                             |
|-----------------------------|----------------------------------------|-------------------------------------|
| `starmap:sync`              | Download and import the latest starmap | Syncs systems, objects, jump points |
| `starmap:translate-systems` | Translate all star systems using DeepL | Requires `DEEPL_AUTH_KEY`           |

## Galactapedia

| Command                        | Description                                            | Options                                                      |
|--------------------------------|--------------------------------------------------------|--------------------------------------------------------------|
| `galactapedia:sync`            | Sync galactapedia categories, articles, and properties | Downloads latest articles                                    |
| `galactapedia:translate`       | Translate all available galactapedia articles          | Requires `DEEPL_AUTH_KEY`                                    |
| `galactapedia:backfill-counts` | Backfill galactapedia article counts from pivot tables | `--chunk` — Articles per chunk (default: `500`), `--dry-run` |

## Statistics

| Command      | Description                            | Options                    |
|--------------|----------------------------------------|----------------------------|
| `stats:sync` | Download and import funding statistics | Updates crowdfunding stats |

## Image Classification

| Command               | Description                                   | Options                                           |
|-----------------------|-----------------------------------------------|---------------------------------------------------|
| `app:classify-images` | Classify Star Citizen images with a local VLM | `--limit` — Max images to process (default: `10`) |

## Sitemap

| Command            | Description                               | Options                                                 |
|--------------------|-------------------------------------------|---------------------------------------------------------|
| `sitemap:generate` | Generate sitemap files for search engines | `--only` — Comma-separated list of segments to generate |

## Data Migration

| Command                     | Description                                                 | Options                      |
|-----------------------------|-------------------------------------------------------------|------------------------------|
| `data:migrate`              | Migrate selected table groups from MariaDB to Postgres      | `--all` — Migrate all tables |
| `data:migrate-translations` | Migrate translations from relational tables to JSON columns | Run after `data:migrate`     |

## Scheduled Tasks

These run automatically via the `scheduler` Docker service. Defined in `routes/console.php`.

| Command                                           | Schedule         | Purpose                                                                 |
|---------------------------------------------------|------------------|-------------------------------------------------------------------------|
| `comm-link:schedule`                              | Every 15 minutes | Download missing Comm-Links and queue imports                           |
| `comm-link:download-new-versions`                 | Yearly           | Re-download all existing Comm-Links                                     |
| `stats:sync`                                      | Daily at 20:00   | Update funding statistics                                               |
| `vehicles:import-ship-matrix`                     | Daily            | Sync ship matrix data, then regenerate vehicles sitemap                 |
| `vehicles:import-msrp`                            | Daily            | Update ship pricing                                                     |
| `vehicles:import-loaner`                          | Daily            | Update loaner mappings                                                  |
| `game:import-item-prices`                         | Daily            | Import prices from UEX Corp API, then regenerate item-related sitemaps  |
| `starmap:sync`                                    | Monthly          | Sync starmap data, then regenerate starsystem/celestial-object sitemaps |
| `galactapedia:sync`                               | Daily at 2:00    | Sync galactapedia articles                                              |
| `galactapedia:translate`                          | Daily at 3:00    | Translate galactapedia content                                          |
| `sitemap:generate --only=comm-links,galactapedia` | Daily at 4:00    | Regenerate comm-link and galactapedia sitemaps                          |
