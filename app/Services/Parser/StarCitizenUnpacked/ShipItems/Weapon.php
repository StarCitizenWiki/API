<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class Weapon extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($item['Weapon'])) {
            return null;
        }

        $modes = [];

        foreach ($item['Weapon']['Modes'] as $mode) {
            $modes[] = [
                'name' => $mode['Name'],
                'localized_name' => $mode['LocalisedName'],
                'fire_type' => $mode['FireType'],
                'rounds_per_minute' => $mode['RoundsPerMinute'],
                'ammo_per_shot' => $mode['AmmoPerShot'],
                'pellets_per_shot' => $mode['PelletsPerShot'],
                'damages' => array_filter([
                    'shot' => array_filter([
                        'physical' => $mode['DamagePerShot']['Physical'] ?? null,
                        'energy' => $mode['DamagePerShot']['Energy'] ?? null,
                        'distortion' => $mode['DamagePerShot']['Distortion'] ?? null,
                        'thermal' => $mode['DamagePerShot']['Thermal'] ?? null,
                        'biochemical' => $mode['DamagePerShot']['Biochemical'] ?? null,
                        'stun' => $mode['DamagePerShot']['Stun'] ?? null,
                    ]),
                    'second' => array_filter([
                        'physical' => $mode['DamagePerSecond']['Physical'] ?? null,
                        'energy' => $mode['DamagePerSecond']['Energy'] ?? null,
                        'distortion' => $mode['DamagePerSecond']['Distortion'] ?? null,
                        'thermal' => $mode['DamagePerSecond']['Thermal'] ?? null,
                        'biochemical' => $mode['DamagePerSecond']['Biochemical'] ?? null,
                        'stun' => $mode['DamagePerSecond']['Stun'] ?? null,
                    ]),
                ]),
            ];
        }

        return [
            'speed' => $item['Weapon']['Ammunition']['Speed'] ?? 0,
            'range' => $item['Weapon']['Ammunition']['Range'] ?? 0,
            'size' => $item['Weapon']['Ammunition']['Size'] ?? 0,
            'capacity' => $item['Weapon']['Ammunition']['Capacity'] ?? 0,
            'damages' => array_filter([
                'default' => array_filter([
                    'physical' => $item['Weapon']['Ammunition']['ImpactDamage']['Physical'] ?? null,
                    'energy' => $item['Weapon']['Ammunition']['ImpactDamage']['Energy'] ?? null,
                    'distortion' => $item['Weapon']['Ammunition']['ImpactDamage']['Distortion'] ?? null,
                    'thermal' => $item['Weapon']['Ammunition']['ImpactDamage']['Thermal'] ?? null,
                    'biochemical' => $item['Weapon']['Ammunition']['ImpactDamage']['Biochemical'] ?? null,
                    'stun' => $item['Weapon']['Ammunition']['ImpactDamage']['Stun'] ?? null,
                ]),
                'detonation' => array_filter([
                    'physical' => $item['Weapon']['Ammunition']['DetonationDamage']['Physical'] ?? null,
                    'energy' => $item['Weapon']['Ammunition']['DetonationDamage']['Energy'] ?? null,
                    'distortion' => $item['Weapon']['Ammunition']['DetonationDamage']['Distortion'] ?? null,
                    'thermal' => $item['Weapon']['Ammunition']['DetonationDamage']['Thermal'] ?? null,
                    'biochemical' => $item['Weapon']['Ammunition']['DetonationDamage']['Biochemical'] ?? null,
                    'stun' => $item['Weapon']['Ammunition']['DetonationDamage']['Stun'] ?? null,
                ]),
            ]),
            'modes' => $modes,
        ];
    }
}
