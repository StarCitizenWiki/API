<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_armor',
    title: 'Vehicle Armor',
    description: 'Armor characteristics for ship and vehicle hull plating. Signal multipliers affect detectability (values < 1.0 reduce signature for stealth, > 1.0 increase signature). Damage multipliers determine resistance to damage types (lower values = more resistant).',
    properties: [
        new OA\Property(
            property: 'signal_infrared',
            description: 'Infrared signature multiplier. Lower values make the ship harder to detect via heat signature.',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'signal_electromagnetic',
            description: 'Electromagnetic signature multiplier. Lower values provide better EM stealth.',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'signal_cross_section',
            description: 'Radar cross-section multiplier. Affects radar detectability.',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'damage_physical',
            description: 'Physical damage multiplier. Typically 0.62, providing 38% damage reduction.',
            type: 'double',
            example: 0.62,
            nullable: true
        ),
        new OA\Property(
            property: 'damage_energy',
            description: 'Energy weapon damage multiplier. Values around 1.0 are neutral.',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'damage_distortion',
            description: 'Distortion damage multiplier. Typically around 1.0.',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'damage_thermal',
            description: 'Thermal damage multiplier. Typically 1.0 (neutral, no resistance).',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'damage_biochemical',
            description: 'Biochemical damage multiplier. Typically 1.0 (neutral, no resistance).',
            type: 'double',
            example: 1.0,
            nullable: true
        ),
        new OA\Property(
            property: 'damage_stun',
            description: 'Stun damage multiplier. Typically 0 (complete immunity to stun).',
            type: 'double',
            example: 0,
            nullable: true
        ),
    ],
    type: 'object'
)]
class ArmorResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $armor = Arr::get($data, 'stdItem.Armor', []);
        $signalMultipliers = Arr::get($armor, 'SignalMultipliers', []);
        $damageMultipliers = Arr::get($armor, 'DamageMultipliers', []);

        return [
            'signal_infrared' => Arr::get($signalMultipliers, 'Infrared'),
            'signal_electromagnetic' => Arr::get($signalMultipliers, 'Electromagnetic'),
            'signal_cross_section' => Arr::get($signalMultipliers, 'CrossSection'),
            'damage_physical' => Arr::get($damageMultipliers, 'Physical'),
            'damage_energy' => Arr::get($damageMultipliers, 'Energy'),
            'damage_distortion' => Arr::get($damageMultipliers, 'Distortion'),
            'damage_thermal' => Arr::get($damageMultipliers, 'Thermal'),
            'damage_biochemical' => Arr::get($damageMultipliers, 'Biochemical'),
            'damage_stun' => Arr::get($damageMultipliers, 'Stun'),
        ];
    }
}
