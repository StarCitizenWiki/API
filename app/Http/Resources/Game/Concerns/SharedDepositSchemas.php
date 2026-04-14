<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Concerns;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'area_exception',
    title: 'Area Exception',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'modifier', type: 'number'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'area_boost',
    title: 'Area Boost',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'global_modifier', type: 'number', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'clustering_param',
    title: 'Clustering Param',
    properties: [
        new OA\Property(property: 'min_size', type: 'number', nullable: true),
        new OA\Property(property: 'max_size', type: 'number', nullable: true),
        new OA\Property(property: 'min_proximity', type: 'number', nullable: true),
        new OA\Property(property: 'max_proximity', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability', type: 'number', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'clustering',
    title: 'Clustering',
    properties: [
        new OA\Property(property: 'key', type: 'string', nullable: true),
        new OA\Property(property: 'min_size', type: 'number', nullable: true),
        new OA\Property(property: 'max_size', type: 'number', nullable: true),
        new OA\Property(property: 'min_proximity', type: 'number', nullable: true),
        new OA\Property(property: 'max_proximity', type: 'number', nullable: true),
        new OA\Property(property: 'probability', type: 'number', nullable: true),
        new OA\Property(property: 'probability_percent', type: 'number', nullable: true),
        new OA\Property(
            property: 'params',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/clustering_param')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'material_entry',
    title: 'Material Entry',
    properties: [
        new OA\Property(property: 'key', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'is_current', type: 'boolean'),
        new OA\Property(property: 'quality_min', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_max', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_mean', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_stddev', type: 'integer', nullable: true),
        new OA\Property(property: 'min_percentage', type: 'number'),
        new OA\Property(property: 'max_percentage', type: 'number'),
        new OA\Property(property: 'instability', type: 'number', nullable: true),
        new OA\Property(property: 'resistance', type: 'number', nullable: true),
        new OA\Property(property: 'group_probability', type: 'number'),
        new OA\Property(property: 'group_probability_percent', type: 'number'),
        new OA\Property(property: 'relative_probability', type: 'number'),
        new OA\Property(property: 'relative_probability_percent', type: 'number'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'deposit_base',
    title: 'Deposit Base',
    properties: [
        new OA\Property(property: 'key', type: 'string'),
        new OA\Property(property: 'label', type: 'string'),
        new OA\Property(property: 'signature', nullable: true),
        new OA\Property(
            property: 'area_exceptions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/area_exception'),
            nullable: true
        ),
        new OA\Property(property: 'clustering', ref: '#/components/schemas/clustering', nullable: true),
        new OA\Property(
            property: 'materials',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/material_entry')
        ),
        new OA\Property(property: 'quality_min', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_max', type: 'integer', nullable: true),
        new OA\Property(property: 'relative_probability_min', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability_max', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability_min_percent', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability_max_percent', type: 'number', nullable: true),
    ],
    type: 'object'
)]
class SharedDepositSchemas {}
