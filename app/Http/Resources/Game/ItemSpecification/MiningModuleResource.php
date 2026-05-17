<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mining_module',
    title: 'Mining Module',
    description: 'Deprecated: Use mining_modifier instead.',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'Mining Module (Active)', nullable: true),
        new OA\Property(property: 'module_type', type: 'string', example: 'Active', nullable: true),
        new OA\Property(
            property: 'usage',
            description: 'Usability details sourced from Item.stdItem.MiningModule (charges/lifetime) and Item.stdItem.DescriptionData (uses/duration).',
            properties: [
                new OA\Property(property: 'charges', description: 'Total activations provided by the module.', type: 'integer', example: 6, nullable: true),
                new OA\Property(property: 'lifetime_seconds', description: 'Lifetime in seconds a module remains effective once installed.', type: 'double', example: 60, nullable: true),
                new OA\Property(property: 'uses', description: 'Number of activations before a consumable expires. Observed range: 3-10.', type: 'integer', example: 6, nullable: true),
                new OA\Property(property: 'duration_seconds', description: 'Active duration per use in seconds. Observed range: 15-60.', type: 'double', example: 30, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'charges',
            description: 'Deprecated: use usage.charges.',
            type: 'integer',
            example: 5,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'lifetime_seconds',
            description: 'Deprecated: use usage.lifetime_seconds.',
            type: 'double',
            example: 3600,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'uses',
            description: 'Deprecated: use usage.uses.',
            type: 'integer',
            example: 5,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'duration_seconds',
            description: 'Deprecated: use usage.duration_seconds.',
            type: 'double',
            example: 60,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'modifiers',
            description: 'Gameplay-impacting modifiers applied while the module is active or installed.',
            properties: [
                new OA\Property(property: 'mining_laser_power_percent', description: 'Multiplicative change to mining laser power. 150 boosts power by 50%; 85 lowers by 15%.', type: 'double', example: 135, nullable: true),
                new OA\Property(property: 'extraction_laser_power_percent', description: 'Multiplier on extraction beam power. Lower values mean slower extraction but often reduce inert material.', type: 'double', example: 95, nullable: true),
                new OA\Property(property: 'optimal_charge_window_percent', description: 'Change to the size of the optimal (green) window. Positive widens, negative shrinks. Range seen: -10 to +40.', type: 'double', example: 40, nullable: true),
                new OA\Property(property: 'optimal_charge_rate_percent', description: 'Change to the ideal charge rate speed. Higher values speed charging; negatives slow it. Range seen: -20 to +60.', type: 'double', example: 60, nullable: true),
                new OA\Property(property: 'all_charge_rates_percent', description: 'Flat multiplier to all charge-rate bands when present. Range seen: +5 to +24.', type: 'double', example: 24, nullable: true),
                new OA\Property(property: 'inert_material_modifier_percent', description: 'Reduction to inert material collected. Positive values in data mean less inert material (e.g., 6, 24).', type: 'double', example: 24, nullable: true),
                new OA\Property(property: 'resistance_percent', description: 'Change to deposit resistance (hardness). Positive makes rock more resistant; negative softens. Range seen: -24.8 to +15.5.', type: 'double', example: -15, nullable: true),
                new OA\Property(property: 'instability_percent', description: 'Change to laser instability. Negative stabilizes, positive increases jitter. Range seen: -20 to +10.', type: 'double', example: -10, nullable: true),
                new OA\Property(property: 'shatter_damage_percent', description: 'Change to damage taken when the rock shatters. Negative reduces hazard; positive increases. Range seen: -30 to +40.', type: 'double', example: -30, nullable: true),
                new OA\Property(property: 'overcharge_rate_percent', description: 'Change to catastrophic/overcharge rate. Negative lowers risk, positive increases. Range seen: -80 to +60.', type: 'double', example: -60, nullable: true),
                new OA\Property(property: 'cluster_factor', description: 'Scaling applied to cluster-related calculations for fracture/extraction when present.', type: 'double', example: 1.35, nullable: true),
                new OA\Property(property: 'damage_multiplier', description: 'General damage multiplier used by modules. Observed range: 0.85-1.50.', type: 'double', example: 1.35, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object',
    deprecated: true
)]
/**
 * @deprecated
 */
class MiningModuleResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $stdItem = $this->extractStdItem($data);
        $description = Arr::get($stdItem, 'DescriptionData', []);
        $miningModule = Arr::get($stdItem, 'MiningModule', []);
        $modifiers = is_array($miningModule) ? Arr::get($miningModule, 'Modifiers', []) : [];

        $legacyModifiers = [
            [
                'name' => 'all_charge_rates',
                'display_name' => 'All Charge Rates',
                'value' => Arr::get($description, 'All Charge Rates', Arr::get($modifiers, 'AllChargeRates')),
            ],
            [
                'name' => 'collection_point_radius',
                'display_name' => 'Collection Point Radius',
                'value' => Arr::get($description, 'Collection Point Radius', Arr::get($modifiers, 'CollectionPointRadius')),
            ],
            [
                'name' => 'instability',
                'display_name' => 'Instability',
                'value' => Arr::get($description, 'Instability', Arr::get($modifiers, 'Instability')),
            ],
            [
                'name' => 'optimal_charge_rate',
                'display_name' => 'Optimal Charge Rate',
                'value' => Arr::get($description, 'Optimal Charge Rate', Arr::get($modifiers, 'OptimalChargeRate')),
            ],
            [
                'name' => 'optimal_charge_window',
                'display_name' => 'Optimal Charge Window',
                'value' => Arr::get($description, 'Optimal Charge Window', Arr::get($description, 'Optimal Charge Window Size', Arr::get($modifiers, 'OptimalChargeWindow'))),
            ],
            [
                'name' => 'overcharge_rate',
                'display_name' => 'Overcharge Rate',
                'value' => Arr::get($description, 'Overcharge Rate', Arr::get($description, 'Catastrophic Charge Rate', Arr::get($modifiers, 'OverchargeRate'))),
            ],
            [
                'name' => 'resistance',
                'display_name' => 'Resistance',
                'value' => Arr::get($description, 'Resistance', Arr::get($modifiers, 'Resistance')),
            ],
            [
                'name' => 'shatter_damage',
                'display_name' => 'Shatter Damage',
                'value' => Arr::get($description, 'Shatter Damage', Arr::get($modifiers, 'ShatterDamage')),
            ],
            [
                'name' => 'extraction_rate',
                'display_name' => 'Extraction Rate',
                'value' => Arr::get($description, 'Extraction Rate', Arr::get($modifiers, 'ExtractionRate')),
            ],
            [
                'name' => 'inert_materials',
                'display_name' => 'Inert Materials',
                'value' => Arr::get($description, 'Inert Materials', Arr::get($modifiers, 'InertMaterials')),
            ],
        ];

        return [
            'type' => Arr::get($description, 'Item Type'),
            'uses' => Arr::get($description, 'Uses'),
            'duration' => Arr::get($description, 'Duration'),
            'modifiers' => array_filter($legacyModifiers, static fn ($value) => $value['value'] !== null),
        ];
    }
}
