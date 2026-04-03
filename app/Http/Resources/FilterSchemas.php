<?php

declare(strict_types=1);

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'filter_value',
    title: 'Filter Value',
    description: 'A value available for filtering along with a human label and result count.',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'value',
            nullable: true,
            oneOf: [
                new OA\Schema(type: 'string'),
                new OA\Schema(type: 'integer'),
                new OA\Schema(type: 'boolean'),
            ]
        ),
        new OA\Property(property: 'label', type: 'string'),
        new OA\Property(property: 'count', type: 'integer'),
    ]
)]
#[OA\Schema(
    schema: 'starmap_filter_value',
    title: 'Starmap Filter Value',
    description: 'A value available for filtering along with a human label.',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'value',
            nullable: true,
            oneOf: [
                new OA\Schema(type: 'string'),
                new OA\Schema(type: 'integer'),
                new OA\Schema(type: 'boolean'),
            ]
        ),
        new OA\Property(property: 'label', type: 'string'),
    ]
)]
final class FilterSchemas {}
