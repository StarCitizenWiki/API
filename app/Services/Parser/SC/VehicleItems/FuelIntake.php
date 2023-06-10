<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class FuelIntake extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemFuelIntakeParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'fuel_push_rate' => Arr::get($data, 'fuelPushRate'),
            'minimum_rate' => Arr::get($data, 'minimumRate'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
