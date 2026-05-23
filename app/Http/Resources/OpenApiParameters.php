<?php

declare(strict_types=1);

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'page',
    name: 'page',
    description: 'Page number for pagination (starts at 1). Prefer using `page[number]` instead.',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
)]
#[OA\Parameter(
    parameter: 'page_number',
    name: 'page[number]',
    description: 'Page number for pagination (starts at 1).',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
)]
#[OA\Parameter(
    parameter: 'page_size',
    name: 'page[size]',
    description: 'Number of results per page. Maximum 200.',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer', default: 30, maximum: 200, minimum: 1)
)]
#[OA\Parameter(
    parameter: 'locale',
    name: 'locale',
    description: 'Locale code for translated fields. Supported values depend on available translations (e.g. en, de, zh).',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string'),
)]
#[OA\Parameter(
    parameter: 'include',
    name: 'include',
    description: 'Comma-separated list of relationships to include. Available includes vary per endpoint, see each endpoint\'s description for supported values.',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string'),
    explode: false,
    allowReserved: true
)]
#[OA\Parameter(
    parameter: 'sort',
    name: 'sort',
    description: 'Comma-separated sort fields. Prefix with `-` for descending. Supported fields vary per endpoint.',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string'),
    explode: false,
    allowReserved: true
)]
#[OA\Parameter(
    parameter: 'version',
    name: 'version',
    description: 'Game version code to scope results to. Omit to use the current default version. Use `GET /api/game-versions` to list available versions and `GET /api/game-versions/default` to discover the default.',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string'),
    explode: false,
    allowReserved: true
)]
final class OpenApiParameters {}
