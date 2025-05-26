<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class Bomb extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemBombParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'arm_time' => Arr::get($data, 'armTime'),
            'ignite_time' => Arr::get($data, 'igniteTime'),
            'collision_delay_time' => Arr::get($data, 'collisionDelayTime'),
            'explosion_safety_distance' => Arr::get($data, 'explosionSafetyDistance'),

            'explosion_radius_min' => Arr::get($data, 'explosionParams.minRadius'),
            'explosion_radius_max' => Arr::get($data, 'explosionParams.maxRadius'),

            'damages' => array_filter([
                'physical' => Arr::get($data, 'explosionParams.damage.DamageInfo.DamagePhysical'),
                'energy' => Arr::get($data, 'explosionParams.damage.DamageInfo.DamageEnergy'),
                'distortion' => Arr::get($data, 'explosionParams.damage.DamageInfo.DamageDistortion'),
                'thermal' => Arr::get($data, 'explosionParams.damage.DamageInfo.DamageThermal'),
                'biochemical' => Arr::get($data, 'explosionParams.damage.DamageInfo.DamageBiochemical'),
                'stun' => Arr::get($data, 'explosionParams.damage.DamageInfo.DamageStun'),
            ]),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
