<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '3.0.0',
    description: 'The community-maintained Star Citizen data API powering [starcitizen.tools](https://starcitizen.tools) and [Star Citizen Wiki](https://star-citizen.wiki).

## Getting Started

- **Base URL:** `https://api.star-citizen.wiki`
- **Format:** JSON
- **Auth:** Most endpoints are public and require no authentication. Some endpoints (e.g. similar image search) require a Sanctum bearer token.
- **Game Versions:** Game data endpoints are version-scoped. Omit the `version` parameter to use the current default version. See `GET /api/game-versions` for available versions.
- **Pagination:** List endpoints return paginated results using `page[number]` and `page[size]` (max 200). Responses include `links` and `meta` pagination metadata.
- **Sorting:** Use `sort` with field names. Prefix with `-` for descending (e.g. `-name`).
- **Filtering:** Use `filter[field]=value` syntax. See each endpoint\'s filter parameters and the corresponding `/filters` endpoints for valid values.
- **Rate Limits:** Search endpoints are rate-limited to 60 requests/minute per IP. Image search endpoints are limited to 10 requests/minute.

## OpenAPI Specification

- YAML: `GET /api/openapi`
- Interactive docs: [docs.star-citizen.wiki](https://docs.star-citizen.wiki)

## Attribution

This API is maintained by the Star Citizen Wiki community. Data is sourced from game files, RSI website, and community contributions.
',
    title: 'Star Citizen Wiki API',
    contact: new OA\Contact(email: 'foxftw@star-citizen.wiki'),
    license: new OA\License(name: 'MIT (code only)', url: 'https://opensource.org/licenses/MIT'),
)]
#[OA\Server(
    url: 'https://api.star-citizen.wiki',
    description: 'Production API',
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Bearer token authentication via Laravel Sanctum personal access tokens. Most endpoints are public and do not require authentication.',
    scheme: 'bearer',
)]
abstract class Controller
{
    /**
     * Clean the name for query use.
     */
    protected function cleanQueryName(string $name): string
    {
        return str_replace('_', ' ', urldecode($name));
    }
}
