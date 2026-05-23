<?php

declare(strict_types=1);

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
        new OA\Property(
            property: 'resource',
            ref: '#/components/schemas/canonical_resource_meta',
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'canonical_resource_meta',
    description: 'Canonical resource identity metadata',
    properties: [
        new OA\Property(property: 'type', description: 'Resource type discriminator.', type: 'string', enum: ['blueprint', 'commodity', 'item', 'location', 'mission', 'vehicle'], example: 'item'),
        new OA\Property(property: 'uuid', description: 'Entity UUID.', type: 'string', example: '97648869-5fa5-42da-b804-4d9314289539'),
        new OA\Property(property: 'slug', description: 'URL-friendly slug. Omitted when null.', type: 'string', example: 'aegs-avenger-stalker', nullable: true),
        new OA\Property(property: 'api_url', description: 'Canonical API URL for this resource.', type: 'string', example: 'https://api.star-citizen.wiki/api/items/aegs-avenger-stalker'),
        new OA\Property(property: 'web_url', description: 'Canonical web URL for this resource.', type: 'string', example: 'https://star-citizen.wiki/items/aegs-avenger-stalker'),
        new OA\Property(property: 'version', description: 'Game version code this response is scoped to. Omitted when not applicable.', type: 'string', example: '4.8.0-LIVE.11825000', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'pagination_links',
    description: 'Pagination navigation links.',
    properties: [
        new OA\Property(property: 'first', description: 'URL to the first page.', type: 'string', nullable: true),
        new OA\Property(property: 'last', description: 'URL to the last page.', type: 'string', nullable: true),
        new OA\Property(property: 'prev', description: 'URL to the previous page.', type: 'string', nullable: true),
        new OA\Property(property: 'next', description: 'URL to the next page.', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'pagination_meta',
    description: 'Pagination metadata.',
    properties: [
        new OA\Property(property: 'current_page', description: 'Current page number.', type: 'integer', example: 1),
        new OA\Property(property: 'from', description: 'Index of the first item in this page.', type: 'integer', example: 1),
        new OA\Property(property: 'last_page', description: 'Last available page number.', type: 'integer', example: 5),
        new OA\Property(property: 'per_page', description: 'Number of items per page.', type: 'integer', example: 30),
        new OA\Property(property: 'to', description: 'Index of the last item in this page.', type: 'integer', example: 30),
        new OA\Property(property: 'total', description: 'Total number of items across all pages.', type: 'integer', example: 150),
        new OA\Property(
            property: 'links',
            description: 'Pagination navigation links with page URLs.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'url', type: 'string', nullable: true),
                    new OA\Property(property: 'label', type: 'string'),
                    new OA\Property(property: 'active', type: 'boolean'),
                ],
                type: 'object',
            ),
        ),
        new OA\Property(property: 'path', description: 'Base URL path for pagination.', type: 'string', example: 'https://api.star-citizen.wiki/api/vehicles'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'error_response',
    description: 'Generic error response.',
    properties: [
        new OA\Property(property: 'message', description: 'Human-readable error message.', type: 'string', example: 'Not found.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'validation_error_response',
    description: 'Validation error response with field-level details.',
    properties: [
        new OA\Property(property: 'message', description: 'Human-readable error message.', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            description: 'Field-level validation errors.',
            type: 'object',
            example: ['filter[query]' => ['The filter[query] field is required.']],
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string'),
            ),
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'unauthenticated_error_response',
    description: 'Authentication error response.',
    properties: [
        new OA\Property(property: 'message', description: 'Human-readable error message.', type: 'string', example: 'Unauthenticated.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'rate_limit_error_response',
    description: 'Rate limit exceeded error response.',
    properties: [
        new OA\Property(property: 'message', description: 'Human-readable error message.', type: 'string', example: 'Too many requests.'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'not_found_error_response',
    description: 'Resource not found error response.',
    properties: [
        new OA\Property(property: 'message', description: 'Human-readable error message.', type: 'string', example: 'No query results for model.'),
    ],
    type: 'object'
)]
final class OpenApiSchemas {}
