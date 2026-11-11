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
    description: 'Game version code to scope results to. Omit to use the current default version. An unknown code returns 422 with an error on the `version` field. Use `GET /api/game-versions` to list available versions and `GET /api/game-versions/default` to discover the default.',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string'),
    explode: false,
    allowReserved: true
)]
#[
    OA\Parameter(
        parameter: 'commodity_sort',
        name: 'sort',
        description: 'Sort field. Prefix with "-" for descending. Supported: key, name, rarity, density, instability, resistance, signature.',
        in: 'query',
        schema: new OA\Schema(type: 'string', example: '-rarity')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_used',
        name: 'filter[used]',
        description: 'When true, only commodities used by blueprint ingredients in the requested or default game version are returned. Example: `true`',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_system',
        name: 'filter[system]',
        description: 'Star system where the commodity can be found (see GET /api/commodities/filters for valid values). Example: `Stanton System`',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_type',
        name: 'filter[type]',
        description: 'Location type name (see GET /api/commodities/filters for valid values). Example: `Planet`',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_rarity',
        name: 'filter[rarity]',
        description: 'Commodity tier/rarity level, lowercase (see GET /api/commodities/filters for valid values). Example: `epic`',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_kind',
        name: 'filter[kind]',
        description: 'Resource kind (see GET /api/commodities/filters for valid values). Example: `mineable`',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_group',
        name: 'filter[group]',
        description: 'Commodity group name. Comma-separated for multiple. Example: `ProcessedGoods,Food`',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_refined_version',
        name: 'filter[refined_version]',
        description: 'Refined version name (see GET /api/commodities/filters for valid values). Example: `Agricium`',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_location',
        name: 'filter[location]',
        description: 'Partial match on starmap location name, or exact starmap location UUID (the `location_uuid` returned by commodity endpoints). Example: `ArcCorp`',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_query',
        name: 'filter[query]',
        description: 'Search commodities by name or key. Example: `Agricium`',
        in: 'query',
        schema: new OA\Schema(type: 'string')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_ship',
        name: 'filter[ship]',
        description: 'When true, only show commodities mineable by ships. Example: `true`',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_ground_vehicle',
        name: 'filter[ground_vehicle]',
        description: 'When true, only show commodities mineable by ground vehicles. Example: `true`',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_fps',
        name: 'filter[fps]',
        description: 'When true, only show commodities mineable on foot (FPS). Example: `true`',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_harvestable',
        name: 'filter[harvestable]',
        description: 'When true, only show commodities collectible from harvestable or plant deposits. Example: `true`',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_salvage',
        name: 'filter[salvage]',
        description: 'When true, only show commodities obtainable through salvage. Example: `false`',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
#[
    OA\Parameter(
        parameter: 'commodity_filter_mineable',
        name: 'filter[mineable]',
        description: 'When true, only show mineable commodities; when false, only show non-mineable. Example: `true`',
        in: 'query',
        schema: new OA\Schema(type: 'boolean')
    )]
final class OpenApiParameters {}
