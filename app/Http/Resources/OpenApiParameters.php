<?php

declare(strict_types=1);

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'page',
    name: 'page',
    description: 'Page number for pagination (starts at 1).',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
)]
#[OA\Parameter(
    parameter: 'locale',
    name: 'locale',
    description: 'Locale code for translated fields (e.g. en, de, zh).',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string')
)]
#[OA\Parameter(
    parameter: 'include',
    name: 'include',
    description: 'Comma-separated list of relationships to include (e.g. manufacturer,translations).',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string'),
    explode: false,
    allowReserved: true
)]
#[OA\Parameter(
    parameter: 'sort',
    name: 'sort',
    description: 'Comma-separated list of fields to sort by. Prefix with - for descending order (e.g. -name,size).',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string'),
    explode: false,
    allowReserved: true
)]
final class OpenApiParameters {}
