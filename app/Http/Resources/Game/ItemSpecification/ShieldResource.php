<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shield_damage_range',
    title: 'Shield Damage Range',
    description: 'Minimum/maximum absorption or resistance values for a given damage type.',
    properties: [
        new OA\Property(property: 'min', type: 'double', example: 0.0, nullable: true),
        new OA\Property(property: 'max', type: 'double', example: 1.0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'shield_damage_map',
    title: 'Shield Damage Map',
    description: 'Per-damage-type values. Only types present in source data are returned.',
    properties: [
        new OA\Property(property: 'physical', ref: '#/components/schemas/shield_damage_range', nullable: true),
        new OA\Property(property: 'energy', ref: '#/components/schemas/shield_damage_range', nullable: true),
        new OA\Property(property: 'distortion', ref: '#/components/schemas/shield_damage_range', nullable: true),
        new OA\Property(property: 'thermal', ref: '#/components/schemas/shield_damage_range', nullable: true),
        new OA\Property(property: 'biochemical', ref: '#/components/schemas/shield_damage_range', nullable: true),
        new OA\Property(property: 'stun', ref: '#/components/schemas/shield_damage_range', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'shield_reserve_pool',
    title: 'Shield Reserve Pool',
    description: 'Reserve pool behavior for shield health and regen.',
    properties: [
        new OA\Property(
            property: 'regen_rate',
            description: 'Reserve pool max regen rate (ReservePool.MaxShieldRegen).',
            type: 'double',
            example: 211,
            nullable: true
        ),
        new OA\Property(
            property: 'regen_time',
            description: 'Reserve pool regeneration time (ReservePool.RegenerationTime).',
            type: 'double',
            example: 20.0,
            nullable: true
        ),

        new OA\Property(property: 'initial_health_ratio', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'max_health_ratio', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'regen_rate_ratio', type: 'double', example: 1, nullable: true),
        new OA\Property(property: 'drain_rate_ratio', type: 'double', example: 2.5, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'shield_regen_delay',
    title: 'Shield Regen Delay',
    description: 'Delay before shield regeneration starts.',
    properties: [
        new OA\Property(property: 'downed', description: 'Delay after shields are fully downed.', type: 'double', example: 8.47, nullable: true),
        new OA\Property(property: 'damage', description: 'Delay after taking damage (without being fully downed).', type: 'double', example: 4.24, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'shield',
    title: 'Shield',
    description: 'Ship shield generator statistics covering health pool, regeneration, reserve pool behavior, recharge delays, and damage absorption/resistance.',
    properties: [
        new OA\Property(
            property: 'max_health',
            description: 'Total shield hit points across all faces (MaxShieldHealth).',
            type: 'double',
            example: 4410,
            nullable: true
        ),
        new OA\Property(
            property: 'regen_rate',
            description: 'Maximum shield regeneration per second (MaxShieldRegen).',
            type: 'double',
            example: 211,
            nullable: true
        ),
        new OA\Property(
            property: 'regen_time',
            description: 'Shield regeneration time in seconds (RegenerationTime), rounded to 2 decimals.',
            type: 'double',
            example: 20.0,
            nullable: true
        ),
        new OA\Property(
            property: 'decay_ratio',
            description: 'Portion of regen lost when shield is taking damage (DecayRatio).',
            type: 'double',
            example: 0.25,
            nullable: true
        ),

        new OA\Property(
            property: 'reserve_pool',
            ref: '#/components/schemas/shield_reserve_pool',
            description: 'Reserve pool behavior for shield health and regen.'
        ),
        new OA\Property(
            property: 'regen_delay',
            ref: '#/components/schemas/shield_regen_delay',
            description: 'Delay before shield regeneration starts.'
        ),

        new OA\Property(
            property: 'electrical_charge_damage_resistance',
            description: 'Additional resistance applied to electrical/EMP style damage (ElectricalChargeDamageResistance).',
            type: 'double',
            example: 0,
            nullable: true
        ),

        new OA\Property(
            property: 'absorption',
            ref: '#/components/schemas/shield_damage_map',
            description: 'Absorption ranges by damage type (from Shield.Absorption.*). Null when no absorption block exists.',
            nullable: true
        ),
        new OA\Property(
            property: 'resistance',
            ref: '#/components/schemas/shield_damage_map',
            description: 'Resistance ranges by damage type (from Shield.Resistance.*). Null when no resistance block exists.',
            nullable: true
        ),

        // Deprecated v2 fields
        new OA\Property(
            property: 'max_shield_health',
            description: 'Deprecated. Use `max_health`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'max_shield_regen',
            description: 'Deprecated. Use `regen_rate`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
class ShieldResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $shield = Arr::get($data, 'stdItem.Shield', []);

        $maxShieldHealth = Arr::get($shield, 'MaxShieldHealth');
        $maxShieldRegen = Arr::get($shield, 'MaxShieldRegen');
        $decayRatio = Arr::get($shield, 'DecayRatio');
        $downedDelay = Arr::get($shield, 'DownedDelay');
        $damagedDelay = Arr::get($shield, 'DamagedDelay');
        $absorptions = Arr::get($shield, 'Absorption', []);

        $reservePool = [
            'regen_rate' => Arr::get($shield, 'ReservePool.MaxShieldRegen'),
            'regen_time' => Arr::get($shield, 'ReservePool.RegenerationTime'),

            'initial_health_ratio' => Arr::get($shield, 'ReservePoolInitialHealthRatio'),
            'max_health_ratio' => Arr::get($shield, 'ReservePoolMaxHealthRatio'),
            'regen_rate_ratio' => Arr::get($shield, 'ReservePoolRegenRateRatio'),
            'drain_rate_ratio' => Arr::get($shield, 'ReservePoolDrainRateRatio'),
        ];

        return [
            'max_health' => $maxShieldHealth,
            'regen_rate' => $maxShieldRegen,
            'regen_time' => Arr::get($shield, 'RegenerationTime', 0),
            'decay_ratio' => $decayRatio,
            'reserve_pool' => $reservePool,
            'regen_delay' => [
                'downed' => $downedDelay,
                'damage' => $damagedDelay,
            ],
            'electrical_charge_damage_resistance' => Arr::get($shield, 'ElectricalChargeDamageResistance'),

            'absorption' => $this->mapAbsorptions($absorptions),
            'resistance' => $this->mapAbsorptions(Arr::get($shield, 'Resistance', [])),

            // Deprecated v2 fields
            'max_shield_health' => $maxShieldHealth,
            'max_shield_regen' => $maxShieldRegen,
        ];
    }

    private function mapAbsorptions(array $absorptions): ?array
    {
        if ($absorptions === []) {
            return null;
        }

        $keys = ['Physical', 'Energy', 'Distortion', 'Thermal', 'Biochemical', 'Stun'];

        $mapped = [];

        foreach ($keys as $key) {
            if (! isset($absorptions[$key])) {
                continue;
            }

            $mapped[strtolower($key)] = [
                'min' => Arr::get($absorptions, "{$key}.Minimum"),
                'max' => Arr::get($absorptions, "{$key}.Maximum"),
            ];
        }

        return $mapped === [] ? null : $mapped;
    }
}
