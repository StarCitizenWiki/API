<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mining_modifier',
    title: 'Mining Modifiers & Mining Gadgets',
    description: '',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'Active', nullable: true),
        new OA\Property(property: 'item_type', type: 'string', example: 'Module', nullable: true),
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
                'cluster_factor' => Arr::get($modifiers, 'ClusterFactor'),
                'overcharge_rate' => Arr::get($modifiers, 'OverchargeRate'),
                'shatter_damage' => Arr::get($modifiers, 'ShatterDamage'),
                'inert_materials' => Arr::get($modifiers, 'InertMaterials'),
            ]),
        ];
    }
}
