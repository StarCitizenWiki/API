<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class Missile extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SCItemMissileParams'])) {
            return null;
        }

        $basePath = 'Components.SCItemMissileParams.';

        return array_filter([
            'signal_type' => $rawData->pull($basePath . 'targetingParams.trackingSignalType'),
            'lock_time' => $rawData->pull($basePath . 'targetingParams.lockTime'),
            'damages' => array_filter([
                'physical' => $rawData->pull($basePath . 'explosionParams.damage.0.DamagePhysical'),
                'energy' => $rawData->pull($basePath . 'explosionParams.damage.0.DamageEnergy'),
                'distortion' => $rawData->pull($basePath . 'explosionParams.damage.0.DamageDistortion'),
                'thermal' => $rawData->pull($basePath . 'explosionParams.damage.0.DamageThermal'),
                'biochemical' => $rawData->pull($basePath . 'explosionParams.damage.0.DamageBiochemical'),
                'stun' => $rawData->pull($basePath . 'explosionParams.damage.0.DamageStun'),
            ]),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
