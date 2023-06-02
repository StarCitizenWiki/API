<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class Thruster extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemThrusterParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'thrust_capacity' => Arr::get($data, 'thrustCapacity'),
            'min_health_thrust_multiplier' => Arr::get($data, 'minHealthThrustMultiplier'),
            'fuel_burn_per_10k_newton' => Arr::get($data, 'fuelBurnRatePer10KNewton'),
            'type' => Arr::get($data, 'thrusterType', 'Main'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
