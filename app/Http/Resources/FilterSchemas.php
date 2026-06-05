<?php

declare(strict_types=1);

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'filter_value',
    title: 'Filter Value',
    description: 'A value available for filtering with a human label and optional result count.',
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
        new OA\Property(property: 'count', description: 'Result count when supplied by the facet.', type: 'integer', nullable: true),
    ],
    type: 'object'
)]
final class FilterSchemas {}
