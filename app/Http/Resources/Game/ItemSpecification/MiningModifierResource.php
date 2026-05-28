<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mining_modifier_value',
    title: 'Mining Modifier Value',
    description: 'Modifier values are returned as-is from game data and may be numeric or string-encoded (for example with %).',
    oneOf: [
        new OA\Schema(type: 'number', format: 'double'),
        new OA\Schema(type: 'string'),
    ]
)]
#[OA\Schema(
    schema: 'mining_modifier_map',
    title: 'Mining Modifier Map',
    description: 'Map of modifier keys to values.',
    type: 'object',
    additionalProperties: new OA\AdditionalProperties(ref: '#/components/schemas/mining_modifier_value')
)]
#[OA\Schema(
    schema: 'mining_modifier',
    title: 'Mining Modifiers & Mining Gadgets',
    description: 'Mining module/gadget specification sourced from stdItem.MiningModule. Includes type, charge/duration metadata, an optional power modifier, and a legacy modifier map.',
    properties: [
        new OA\Property(
            property: 'type',
            description: 'Modifier type (from MiningModule.Type).',
            type: 'string',
            example: 'Active',
            nullable: true
        ),
        new OA\Property(
            property: 'item_type',
            description: 'Derived from classification: `Gadget` when classification is `Mining.Gadget`, otherwise `Module`.',
            type: 'string',
            enum: ['Gadget', 'Module'],
            example: 'Module',
            nullable: true
        ),
        new OA\Property(
            property: 'charges',
            description: 'Remaining charges (MiningModule.Charges). `null` when the source value is -1 (unlimited).',
            type: 'integer',
            example: 3,
            nullable: true
        ),
        new OA\Property(
            property: 'duration',
            description: 'Lifetime/duration in seconds (MiningModule.Lifetime).',
            type: 'double',
            example: 60.0,
            nullable: true,
            x: ['suffix' => ' s']
        ),
        new OA\Property(
            property: 'power_modifier',
            description: 'Power modifier value from Modifiers.DamageMultiplierChange. Returned as-is from the source data.',
            example: 1.1,
            nullable: true,
            oneOf: [
                new OA\Schema(type: 'number', format: 'double'),
                new OA\Schema(type: 'string'),
            ]
        ),
        new OA\Property(
            property: 'modifier_map',
            ref: '#/components/schemas/mining_modifier_map',
            description: 'Flattened modifier map. Known keys: resistance, laser_instability, optimal_charge_window_size, optimal_charge_rate, cluster_factor, overcharge_rate, shatter_damage, inert_materials.'
        ),
    ],
    type: 'object'
)]
class MiningModifierResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $stdItem = $this->extractStdItem($data);
        $modifier = Arr::get($stdItem, 'MiningModule', []);

        $modifiers = Arr::get($modifier, 'Modifiers', []);

        return [
            'type' => Arr::get($modifier, 'Type'),
            'item_type' => Arr::get($data, 'classification') === 'Mining.Gadget' ? 'Gadget' : 'Module',
            'charges' => Arr::get($modifier, 'Charges') === -1 ? null : Arr::get($modifier, 'Charges'),
            'duration' => Arr::get($modifier, 'Lifetime'),
            'power_modifier' => Arr::get($modifiers, 'DamageMultiplierChange'),

            'modifier_map' => array_filter([
                'resistance' => Arr::get($modifiers, 'Resistance'),
                'laser_instability' => Arr::get($modifiers, 'Instability'),
                'optimal_charge_window_size' => Arr::get($modifiers, 'OptimalChargeWindow'),
                'optimal_charge_rate' => Arr::get($modifiers, 'OptimalChargeRate'),
                'all_charge_rates' => Arr::get($modifiers, 'AllChargeRates'),
                'cluster_factor' => Arr::get($modifiers, 'ClusterFactor'),
                'overcharge_rate' => Arr::get($modifiers, 'OverchargeRate'),
                'shatter_damage' => Arr::get($modifiers, 'ShatterDamage'),
                'inert_materials' => Arr::get($modifiers, 'InertMaterials'),
            ]),
        ];
    }
}
