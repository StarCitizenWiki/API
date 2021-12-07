<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class Thruster extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SCItemThrusterParams'])) {
            return null;
        }

        $basePath = 'Components.SCItemThrusterParams.';

        return array_filter([
            'thrust_capacity' => $rawData->pull($basePath . 'thrustCapacity'),
            'min_health_thrust_multiplier' => $rawData->pull($basePath . 'minHealthThrustMultiplier'),
            'fuel_burn_per_10k_newton' => $rawData->pull($basePath . 'fuelBurnRatePer10KNewton'),
            'type' => $rawData->pull($basePath . 'thrusterType', 'Main'),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
