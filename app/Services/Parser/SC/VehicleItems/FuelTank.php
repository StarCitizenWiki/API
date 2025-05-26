<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class FuelTank extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemFuelTankParams');

        if ($data === null) {
            return null;
        }

        $container = self::get($item, 'ResourceContainer.capacity.SStandardCargoUnit');

        if ($container === null) {
            return null;
        }

        $resource = self::get($item, 'ItemResourceComponentParams.states.ItemResourceState.deltas.ItemResourceDeltaStorage');

        $generation = Arr::get($resource, 'generation.resourceAmountPerSecond.SStandardResourceUnit.standardResourceUnits', 0);
        $consumption = Arr::get($resource, 'consumption.resourceAmountPerSecond.SStandardResourceUnit.standardResourceUnits', 0);

        return array_filter([
            'fill_rate' => $generation,
            'drain_rate' => $consumption,
            'capacity' => Arr::get($container, 'standardCargoUnits'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
