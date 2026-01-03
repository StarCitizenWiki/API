<?php

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'metadata',
    title: 'Metadata',
    description: 'Information about version and update date',
    properties: [
        new OA\Property(
            property: 'updated_at',
            description: 'Timestamp this data was last updated.',
            type: 'datetime',
        ),
        new OA\Property(
            property: 'version',
            description: 'The Game Version this item exists in.',
            type: 'string',
        ),
        new OA\Property(
            property: 'deprecated_fields',
            description: 'Deprecated fields that will be removed in future versions.',
            type: 'string',
        ),
    ],
    type: 'object'
)]
final class OpenApiSchemas {}
