<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class Missile extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemMissileParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'signal_type' => Arr::get($data, 'targetingParams.trackingSignalType'),
            'lock_time' => Arr::get($data, 'targetingParams.lockTime'),
            'damages' => array_filter([
                'physical' => Arr::get($data, 'explosionParams.damage.0.DamagePhysical'),
                'energy' => Arr::get($data, 'explosionParams.damage.0.DamageEnergy'),
                'distortion' => Arr::get($data, 'explosionParams.damage.0.DamageDistortion'),
                'thermal' => Arr::get($data, 'explosionParams.damage.0.DamageThermal'),
                'biochemical' => Arr::get($data, 'explosionParams.damage.0.DamageBiochemical'),
                'stun' => Arr::get($data, 'explosionParams.damage.0.DamageStun'),
            ]),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
