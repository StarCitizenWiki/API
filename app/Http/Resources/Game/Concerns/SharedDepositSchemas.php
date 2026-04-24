<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Concerns;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'area_exception',
    title: 'Area Exception',
    description: 'An area-specific modifier that overrides the default deposit probability for a specific resource.',
    properties: [
        new OA\Property(property: 'name', description: 'Name of the area where the exception applies.', type: 'string', nullable: true),
        new OA\Property(property: 'modifier', description: 'Probability multiplier applied within this area (e.g. 2.0 = double chance).', type: 'number'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'area_boost',
    title: 'Area Boost',
    description: 'An area with a global modifier that boosts deposit spawn rates above the baseline.',
    properties: [
        new OA\Property(property: 'name', description: 'Name of the boosted area.', type: 'string', nullable: true),
        new OA\Property(property: 'global_modifier', description: 'Global probability multiplier for this area (values above 1 indicate a boost).', type: 'number', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'clustering_param',
    title: 'Clustering Param',
    description: 'A single clustering variation with its own size, proximity, and probability settings.',
    properties: [
        new OA\Property(property: 'min_size', description: 'Minimum cluster size.', type: 'number', nullable: true),
        new OA\Property(property: 'max_size', description: 'Maximum cluster size.', type: 'number', nullable: true),
        new OA\Property(property: 'min_proximity', description: 'Minimum distance between deposit instances in the cluster.', type: 'number', nullable: true),
        new OA\Property(property: 'max_proximity', description: 'Maximum distance between deposit instances in the cluster.', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability', description: 'Relative probability weight for this clustering variation.', type: 'number', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'clustering',
    title: 'Clustering',
    description: 'Defines how deposits are grouped into clusters, including size, proximity, and probability parameters.',
    properties: [
        new OA\Property(property: 'key', description: 'Internal clustering configuration key.', type: 'string', nullable: true),
        new OA\Property(property: 'min_size', description: 'Minimum number of deposits in a cluster.', type: 'number', nullable: true),
        new OA\Property(property: 'max_size', description: 'Maximum number of deposits in a cluster.', type: 'number', nullable: true),
        new OA\Property(property: 'min_proximity', description: 'Minimum distance between clustered deposits.', type: 'number', nullable: true),
        new OA\Property(property: 'max_proximity', description: 'Maximum distance between clustered deposits.', type: 'number', nullable: true),
        new OA\Property(property: 'probability', description: 'Raw probability of clustering occurring (0–1).', type: 'number', nullable: true),
        new OA\Property(property: 'probability_percent', description: 'Clustering probability expressed as a percentage (0–100).', type: 'number', nullable: true),
        new OA\Property(
            property: 'params',
            description: 'List of clustering variations with individual probability weights.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/clustering_param')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'material_entry',
    title: 'Material Entry',
    description: 'A single material (commodity) found within a deposit, including quality range and probability data.',
    properties: [
        new OA\Property(property: 'key', description: 'Unique material key identifier.', type: 'string', nullable: true),
        new OA\Property(property: 'name', description: 'Display name of the material.', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', description: 'UUID of the commodity this material represents.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'is_current', description: 'Whether this material is the primary commodity being viewed.', type: 'boolean'),
        new OA\Property(property: 'quality_min', description: 'Minimum quality value of this material in the deposit.', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_max', description: 'Maximum quality value of this material in the deposit.', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_mean', description: 'Average quality value of this material.', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_stddev', description: 'Standard deviation of the quality distribution.', type: 'integer', nullable: true),
        new OA\Property(property: 'min_percentage', description: 'Minimum composition percentage of this material (0–100).', type: 'number'),
        new OA\Property(property: 'max_percentage', description: 'Maximum composition percentage of this material (0–100).', type: 'number'),
        new OA\Property(property: 'instability', description: 'Instability rating of this material, affecting mining difficulty.', type: 'number', nullable: true),
        new OA\Property(property: 'resistance', description: 'Resistance rating of this material, affecting mining difficulty.', type: 'number', nullable: true),
        new OA\Property(property: 'group_probability', description: 'Raw probability of this material occurring in the deposit group (0–1).', type: 'number'),
        new OA\Property(property: 'group_probability_percent', description: 'Group probability expressed as a percentage (0–100).', type: 'number'),
        new OA\Property(property: 'relative_probability', description: 'Raw relative probability compared to other materials in the deposit (0–1).', type: 'number'),
        new OA\Property(property: 'relative_probability_percent', description: 'Relative probability expressed as a percentage (0–100).', type: 'number'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'harvestable_setup',
    title: 'Harvestable Setup',
    description: 'Respawn and despawn timing configuration for harvestable deposit instances.',
    properties: [
        new OA\Property(property: 'respawn_seconds', description: 'Time in seconds before a harvested instance respawns.', type: 'integer', nullable: true),
        new OA\Property(property: 'respawn_formatted', description: 'Human-readable respawn duration (e.g. "5m 30s").', type: 'string', nullable: true),
        new OA\Property(property: 'despawn_seconds', description: 'Time in seconds before an uncollected instance despawns.', type: 'integer', nullable: true),
        new OA\Property(property: 'despawn_formatted', description: 'Human-readable despawn duration (e.g. "10m").', type: 'string', nullable: true),
        new OA\Property(property: 'relative_probability', description: 'Raw relative spawn probability for this harvestable (0–1).', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability_percent', description: 'Relative spawn probability expressed as a percentage (0–100).', type: 'number', nullable: true),
        new OA\Property(property: 'respawn_multiplier', description: 'Multiplier applied to the base respawn time.', type: 'number', nullable: true),
        new OA\Property(property: 'additional_wait_seconds', description: 'Extra wait time in seconds added when nearby players are present.', type: 'integer', nullable: true),
        new OA\Property(property: 'additional_wait_formatted', description: 'Human-readable additional wait duration (e.g. "2m").', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'deposit_base',
    title: 'Deposit Base',
    description: 'Base schema for a mineable or harvestable deposit, shared across commodity and starmap resources.',
    properties: [
        new OA\Property(property: 'key', description: 'Unique deposit key identifier.', type: 'string'),
        new OA\Property(property: 'label', description: 'Human-readable deposit name derived from the key.', type: 'string'),
        new OA\Property(property: 'signature', description: 'Electromagnetic signature strength of the deposit.', type: 'integer', nullable: true),
        new OA\Property(
            property: 'area_exceptions',
            description: 'Area-specific probability modifiers that override the default for this deposit.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/area_exception'),
            nullable: true
        ),
        new OA\Property(property: 'clustering', ref: '#/components/schemas/clustering', description: 'Clustering configuration for deposit groupings, if applicable.', nullable: true),
        new OA\Property(
            property: 'harvestable_setup',
            ref: '#/components/schemas/harvestable_setup',
            description: 'Respawn and despawn timing data for harvestable deposits.',
            nullable: true
        ),
        new OA\Property(
            property: 'provider_names',
            description: 'List of unique provider names that generate this deposit.',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(
            property: 'materials',
            description: 'List of materials (commodities) found in this deposit with their quality and probability data.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/material_entry')
        ),
        new OA\Property(property: 'quality_min', description: 'Minimum quality across all instances of this deposit.', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_max', description: 'Maximum quality across all instances of this deposit.', type: 'integer', nullable: true),
        new OA\Property(property: 'relative_probability_min', description: 'Lowest relative probability among deposit instances (0–1).', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability_max', description: 'Highest relative probability among deposit instances (0–1).', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability_min_percent', description: 'Lowest relative probability as a percentage (0–100).', type: 'number', nullable: true),
        new OA\Property(property: 'relative_probability_max_percent', description: 'Highest relative probability as a percentage (0–100).', type: 'number', nullable: true),
    ],
    type: 'object'
)]
class SharedDepositSchemas {}
