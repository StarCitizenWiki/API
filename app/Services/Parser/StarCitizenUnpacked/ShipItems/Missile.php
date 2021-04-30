<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class Missile extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($item['Missile'])) {
            return null;
        }

        return [
            'damage_physical' => $item['Missile']['Damage']['Physical'] ?? null,
            'damage_energy' => $item['Missile']['Damage']['Energy'] ?? null,
            'damage_distortion' => $item['Missile']['Damage']['Distortion'] ?? null,
            'damage_thermal' => $item['Missile']['Damage']['Thermal'] ?? null,
            'damage_biochemical' => $item['Missile']['Damage']['Biochemical'] ?? null,
            'damage_stun' => $item['Missile']['Damage']['Stun'] ?? null,
        ];
    }
}
