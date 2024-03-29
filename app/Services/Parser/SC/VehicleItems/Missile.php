<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

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

            'lock_range_max' => Arr::get($data, 'targetingParams.lockRangeMax'),
            'lock_range_min' => Arr::get($data, 'targetingParams.lockRangeMin'),
            'lock_angle' => Arr::get($data, 'targetingParams.lockingAngle'),
            'tracking_signal_min' => Arr::get($data, 'targetingParams.trackingSignalMin'),
            'speed' => Arr::get($data, 'GCSParams.linearSpeed'),
            'fuel_tank_size' => Arr::get($data, 'GCSParams.fuelTankSize'),
            'explosion_radius_min' => Arr::get($data, 'explosionParams.minPhysRadius'),
            'explosion_radius_max' => Arr::get($data, 'explosionParams.maxPhysRadius'),

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
