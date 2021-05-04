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

        return [
            'speed' => $item['Weapon']['Ammunition']['Speed'] ?? 0,
            'range' => $item['Weapon']['Ammunition']['Range'] ?? 0,
            'size' => $item['Weapon']['Ammunition']['Size'] ?? 0,
            'capacity' => $item['Weapon']['Ammunition']['Capacity'] ?? 0,
            // TODO Refactor
            'damages' => array_filter([
                'impact' => array_filter([
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
            'modes' => self::buildModesPart($item),
        ];
    }

    private static function buildModesPart($weapon): array
    {
        if (!isset($weapon['Weapon']['Modes'])) {
            return [];
        }

        $modes = collect($weapon['Weapon']['Modes'])
            ->map(function (array $mode) {

                return [
                    'mode' => $mode['Name'],
                    'localised' => $mode['LocalisedName'],
                    'type' => $mode['FireType'],
                    'rounds_per_minute' => $mode['RoundsPerMinute'],
                    'ammo_per_shot' => $mode['AmmoPerShot'],
                    'pellets_per_shot' => $mode['PelletsPerShot'],
                ];
            });

        return $modes->toArray();
    }
}
