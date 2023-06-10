<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class Armor extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemVehicleArmorParams');

        if ($data === null) {
            return null;
        }

        return [
            'signal_infrared' => Arr::get($data, 'signalInfrared'),
            'signal_electromagnetic' => Arr::get($data, 'signalElectromagnetic'),
            'signal_cross_section' => Arr::get($data, 'signalCrossSection'),
            'damage_physical' => Arr::get($data, 'damageMultiplier.DamageInfo.DamagePhysical'),
            'damage_energy' => Arr::get($data, 'damageMultiplier.DamageInfo.DamageEnergy'),
            'damage_distortion' => Arr::get($data, 'damageMultiplier.DamageInfo.DamageDistortion'),
            'damage_thermal' => Arr::get($data, 'damageMultiplier.DamageInfo.DamageThermal'),
            'damage_biochemical' => Arr::get($data, 'damageMultiplier.DamageInfo.DamageBiochemical'),
            'damage_stun' => Arr::get($data, 'damageMultiplier.DamageInfo.DamageStun'),
        ];
    }
}
