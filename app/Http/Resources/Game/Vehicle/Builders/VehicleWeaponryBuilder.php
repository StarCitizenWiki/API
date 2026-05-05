<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Builders;

use Illuminate\Support\Arr;

/**
 * @internal
 *
 * Pure-function pipeline for building weapon-related vehicle data:
 * weaponry stats, damage-type ranges, power pools, and turret decoration.
 */
final class VehicleWeaponryBuilder
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function buildWeaponry(array $payload): array
    {
        $weaponry = Arr::get($payload, 'Weaponry', []);

        $result = array_filter([
            'pilot_dps' => Arr::get($weaponry, 'PilotDps'),
            'pilot_alpha' => Arr::get($weaponry, 'PilotAlpha'),
            'pilot_sustained_dps' => Arr::get($weaponry, 'PilotSustainedDps'),
            'turret_dps' => Arr::get($weaponry, 'TurretDps'),
            'turret_alpha' => Arr::get($weaponry, 'TurretAlpha'),
            'turret_sustained_dps' => Arr::get($weaponry, 'TurretSustainedDps'),
        ], static fn (mixed $value): bool => $value !== null);

        $fixedWeapons = Arr::get($weaponry, 'FixedWeapons');
        if (is_array($fixedWeapons)) {
            $result['fixed_weapons'] = [
                'dps_total' => Arr::get($fixedWeapons, 'DpsTotal'),
                'sustained_dps_total' => Arr::get($fixedWeapons, 'SustainedDpsTotal'),
                'alpha_total' => Arr::get($fixedWeapons, 'AlphaTotal'),
                'weapons' => array_map(static fn (array $weapon): array => [
                    'name' => Arr::get($weapon, 'Name'),
                    'dps' => Arr::get($weapon, 'Dps'),
                    'sustained_dps' => Arr::get($weapon, 'SustainedDps'),
                    'alpha' => Arr::get($weapon, 'Alpha'),
                ], Arr::get($fixedWeapons, 'Weapons', [])),
            ];
        }

        $missiles = Arr::get($weaponry, 'Missiles');
        if (is_array($missiles)) {
            $result['missiles'] = [
                'count' => Arr::get($missiles, 'Count'),
                'damage' => [
                    'physical' => Arr::get($missiles, 'Damage.Physical'),
                    'energy' => Arr::get($missiles, 'Damage.Energy'),
                    'distortion' => Arr::get($missiles, 'Damage.Distortion'),
                    'thermal' => Arr::get($missiles, 'Damage.Thermal'),
                    'biochemical' => Arr::get($missiles, 'Damage.Biochemical'),
                    'stun' => Arr::get($missiles, 'Damage.Stun'),
                    'total' => Arr::get($missiles, 'Damage.Total'),
                ],
            ];
        }

        $totalMissiles = Arr::get($weaponry, 'TotalMissiles');
        if ($totalMissiles !== null) {
            $result['total_missile_damage'] = $totalMissiles;
        }

        return $result;
    }

    /**
     * Build damage-type range data (minimum/maximum per type).
     * Used for shield resistance and absorption.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, array{minimum: mixed, maximum: mixed}>
     */
    public function buildDamageTypeRange(array $payload, string $path): array
    {
        $damageTypes = ['Physical', 'Energy', 'Distortion', 'Thermal', 'Biochemical', 'Stun'];

        $parent = Arr::get($payload, $path, []);

        $result = [];
        foreach ($damageTypes as $type) {
            $entry = Arr::get($parent, $type);
            $result[strtolower($type)] = [
                'minimum' => Arr::get($entry, 'Minimum'),
                'maximum' => Arr::get($entry, 'Maximum'),
            ];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function buildPowerPools(array $payload): array
    {
        $powerPools = Arr::get($payload, 'PowerPools', []);

        return array_map(static fn (array $poolData) => [
            'type' => Arr::get($poolData, 'Type'),
            'item_type' => Arr::get($poolData, 'ItemType'),
            'size' => Arr::get($poolData, 'Size'),
        ], $powerPools);
    }

    /**
     * @param  array<int, mixed>  $entries
     * @return array<int, array<string, mixed>>
     */
    public function decorateTurretEntries(array $entries, string $category): array
    {
        return collect($entries)
            ->filter(static fn (mixed $entry): bool => is_array($entry))
            ->map(static fn (array $entry): array => [
                ...$entry,
                'Category' => $category,
            ])
            ->values()
            ->all();
    }
}
