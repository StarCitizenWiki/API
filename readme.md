# Star Citizen Wiki API

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4.svg)](https://php.net)
[![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20.svg)](https://laravel.com)
[![Docker Build](https://github.com/StarCitizenWiki/API/actions/workflows/docker.yml/badge.svg)](https://github.com/StarCitizenWiki/API/actions/workflows/docker.yml)

The Star Citizen Wiki / Tools API for Star Citizen game data – vehicles, items, manufacturers, star systems, missions, commodities, comm-links, and galactapedia entries.

Interactive **Swagger API documentation:** [docs.star-citizen.wiki](https://docs.star-citizen.wiki)


## Quick Example

```bash
curl https://api.star-citizen.wiki/api/vehicles?filter[name]=Arrow
```

```json
{
  "data": [
    {
      "uuid": "...",
      "name": "Arrow",
      "class_name": "AEGS_Arrow",
      "manufacturer": {
        "name": "Aegis Dynamics",
        "code": "AEGS"
      },
      "classification": "fighter",
      "crew": 1,
      "mass": 26357.0,
      "cargo_capacity": 0.0
    }
  ]
}
```


## Features

- **Game Data**: Items, vehicles, manufacturers, missions, starmap locations, and specifications from [ScDataDumper](https://github.com/octfx/ScDataDumper) game files
- **Starmap**: Star systems, celestial objects, and jump points
- **Comm-Link Archive**: Automated downloading, importing, and translating of official RSI Comm-Links
- **Galactapedia**: Synchronized articles with translation support
- **Ship Matrix & Pricing**: Daily updates of ship specifications, MSRP, and loaner data
- **Multi-language**: English, German, and Chinese Simplified translations
- **RESTful API**: Game-Versioned endpoints with filtering, sorting, and includes ([docs](https://docs.star-citizen.wiki))


## Tech Stack

| Component        | Technology                |
|------------------|---------------------------|
| Runtime          | PHP 8.4+                  |
| Framework        | Laravel 13                |
| Database         | PostgreSQL 16             |
| Queue            | Database / Redis          |
| Cache            | Database / Redis          |
| Auth             | Laravel Sanctum + Fortify |
| Translations     | DeepL API                 |
| Search           | Spatie Query Builder      |
| Containerization | Docker + Docker Compose   |


## Quick Start

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- Docker Compose 2.0+
- Git

### Installation

```bash
git clone https://github.com/StarCitizenWiki/API.git
cd API
git submodule update --init --recursive
cp .env.example .env
```

Edit `.env` and set at minimum:

```dotenv
APP_NAME='Star Citizen API'
APP_URL=http://localhost:8080

DB_DATABASE=api
DB_USERNAME=api
DB_PASSWORD=secret

POSTGRES_DB=api
POSTGRES_USER=api
POSTGRES_PASSWORD=secret
```

> The `DB_*` and `POSTGRES_*` values must match. For the full configuration reference, see [docs/configuration.md](docs/configuration.md).

### Start Services

```bash
docker compose up -d
docker compose exec api php artisan key:generate
docker compose exec api php artisan migrate
docker compose exec api php artisan game:add-version --default <SC_UNPACKED_DATA_VERSION>
docker compose exec api php artisan db:seed
docker compose exec api php artisan game:sync
```

Visit [http://localhost:8080](http://localhost:8080) to verify.


## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Make your changes and add tests
4. Run the test suite (`php artisan test --compact`)
5. Run the linter (`vendor/bin/pint --dirty`)
6. Open a pull request


## Documentation

| Document                                          | Description                                      |
|---------------------------------------------------|--------------------------------------------------|
| [API Reference](https://docs.star-citizen.wiki)   | Interactice swagger api endpoint documentation   |
| [Configuration](docs/configuration.md)            | Environment variable reference                   |
| [Artisan Commands](docs/commands.md)              | All custom commands and scheduled tasks          |
| [Docker Guide](docs/docker.md)                    | Architecture, queues, volumes, and Traefik setup |
| [v2 Migration](docs/migration-v2.md)              | Migrating from v2 to v3                          |


## Third-Party Data & Attributions

### StarCitizenDeutsch: German Translations

**Source:** [rjcncpt/StarCitizen-Deutsch-INI](https://github.com/rjcncpt/StarCitizen-Deutsch-INI)
**License:** [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)

German translations loaded from `global.ini` files during item import. Integrated as a git submodule at `storage/app/api/StarCitizenDeutsch/`.

### ScToolBoxLocales: Chinese Simplified Translations

**Source:** [StarCitizenToolBox/LocalizationData](https://github.com/StarCitizenToolBox/LocalizationData)

Chinese Simplified translations maintained by the StarCitizenToolBox community. Located at `storage/app/api/ScToolBoxLocales/`.

### scunpacked-data: Game Data

**Source:** [StarCitizenWiki/scunpacked-data](https://github.com/StarCitizenWiki/scunpacked-data)

Unpacked Star Citizen game data in JSON format: items, vehicles, manufacturers, labels, and tags. Primary data source for all game content imports. Located at `storage/app/api/scunpacked-data/`.

### Inspiration

Datatables inspired by [spviewer.eu](https://spviewer.eu).


## License

This project is licensed under the [MIT License](LICENSE).

German translations are &copy; Star Citizen Community Translation Team (rjcncpt and contributors), licensed under [CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/). ScToolBoxLocales and scunpacked-data are community-maintained; verify licensing independently for commercial use.


## Legal Notice

This is a fan-made tool and is not affiliated with or endorsed by Cloud Imperium Games or Roberts Space Industries. Star Citizen&reg;, Roberts Space Industries&reg;, and Cloud Imperium&reg; are registered trademarks of Cloud Imperium Rights LLC and Cloud Imperium Rights Ltd. All game data and translations are used for informational and educational purposes only.
