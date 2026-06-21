<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Builders;

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
        $weaponry = $payload['Weaponry'] ?? [];

        $result = array_filter([
            'pilot_dps' => $weaponry['PilotDps'] ?? null,
            'pilot_alpha' => $weaponry['PilotAlpha'] ?? null,
            'pilot_sustained_dps' => $weaponry['PilotSustainedDps'] ?? null,
            'turret_dps' => $weaponry['TurretDps'] ?? null,
            'turret_alpha' => $weaponry['TurretAlpha'] ?? null,
            'turret_sustained_dps' => $weaponry['TurretSustainedDps'] ?? null,
        ], static fn (mixed $value): bool => $value !== null);

        $fixedWeapons = $weaponry['FixedWeapons'] ?? null;
        if (is_array($fixedWeapons)) {
            $result['fixed_weapons'] = [
                'dps_total' => $fixedWeapons['DpsTotal'] ?? null,
                'sustained_dps_total' => $fixedWeapons['SustainedDpsTotal'] ?? null,
                'alpha_total' => $fixedWeapons['AlphaTotal'] ?? null,
                'weapons' => array_map(static fn (array $weapon): array => [
                    'name' => $weapon['Name'] ?? null,
                    'dps' => $weapon['Dps'] ?? null,
                    'sustained_dps' => $weapon['SustainedDps'] ?? null,
                    'alpha' => $weapon['Alpha'] ?? null,
                ], $fixedWeapons['Weapons'] ?? []),
            ];
        }

        $missiles = $weaponry['Missiles'] ?? null;
        if (is_array($missiles)) {
            $missileDmg = $missiles['Damage'] ?? [];
            $result['missiles'] = [
                'count' => $missiles['Count'] ?? null,
                'damage' => [
                    'physical' => $missileDmg['Physical'] ?? null,
                    'energy' => $missileDmg['Energy'] ?? null,
                    'distortion' => $missileDmg['Distortion'] ?? null,
                    'thermal' => $missileDmg['Thermal'] ?? null,
                    'biochemical' => $missileDmg['Biochemical'] ?? null,
                    'stun' => $missileDmg['Stun'] ?? null,
                    'total' => $missileDmg['Total'] ?? null,
                ],
            ];
        }

        $totalMissiles = $weaponry['TotalMissiles'] ?? null;
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

        $parent = $payload;
        if ($path !== '') {
            foreach (explode('.', $path) as $segment) {
                $parent = is_array($parent) && array_key_exists($segment, $parent) ? $parent[$segment] : [];
            }
        }

        $result = [];
        foreach ($damageTypes as $type) {
            $entry = $parent[$type] ?? null;
            $result[strtolower($type)] = [
                'minimum' => $entry['Minimum'] ?? null,
                'maximum' => $entry['Maximum'] ?? null,
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
        $powerPools = $payload['PowerPools'] ?? [];

        return array_map(static fn (array $poolData) => [
            'type' => $poolData['Type'] ?? null,
            'item_type' => $poolData['ItemType'] ?? null,
            'size' => $poolData['Size'] ?? null,
        ], $powerPools);
    }

    /**
     * @param  array<int, mixed>  $entries
     * @return array<int, array<string, mixed>>
     */
    public function decorateTurretEntries(array $entries, string $category): array
    {
        $result = [];

        foreach ($entries as $entry) {
            if (is_array($entry)) {
                $result[] = [...$entry, 'Category' => $category];
            }
        }

        return $result;
    }
}
