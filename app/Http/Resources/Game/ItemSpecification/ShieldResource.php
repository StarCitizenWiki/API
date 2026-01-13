<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shield',
    title: 'Shield',
    description: 'Ship shield generator statistics covering health pool, regeneration, reserve pool behavior, and recharge delays.',
    properties: [
        new OA\Property(
            property: 'max_health',
            description: 'Total shield hit points across all faces.',
            type: 'double',
            example: 4410,
            nullable: true
        ),
        new OA\Property(
            property: 'regen_rate',
            description: 'Maximum shield regeneration per second.',
            type: 'double',
            example: 211,
            nullable: true
        ),
        new OA\Property(
            property: 'decay_ratio',
            description: 'Portion of regen lost when shield is taking damage. Current game data uses 0.25 for all generators.',
            type: 'double',
            example: 0.25,
            nullable: true
        ),
        new OA\Property(
            property: 'reserve_pool',
            description: 'Reserve pool behavior for shield health and regen.',
            properties: [
                new OA\Property(property: 'initial_health_ratio', type: 'double', example: 1, nullable: true),
                new OA\Property(property: 'max_health_ratio', type: 'double', example: 1, nullable: true),
                new OA\Property(property: 'regen_rate_ratio', type: 'double', example: 1, nullable: true),
                new OA\Property(property: 'drain_rate_ratio', type: 'double', example: 2.5, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'regen_delay',
            description: 'Delay before shield regeneration starts.',
            properties: [
                new OA\Property(property: 'downed', type: 'double', example: 8.47, nullable: true),
                new OA\Property(property: 'damage', type: 'double', example: 4.24, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'electrical_charge_damage_resistance',
            description: 'Additional resistance applied to electrical/EMP style damage.',
            type: 'double',
            example: 0,
            nullable: true
        ),

        new OA\Property(property: 'max_shield_health', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'max_shield_regen', type: 'double', nullable: true, deprecated: true),
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
            'regen_time' => round(Arr::get($shield, 'RegenerationTime', 0), 2),
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
