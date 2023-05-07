<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

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

        return array_filter([
            'fill_rate' => Arr::get($data, 'fillRate'),
            'drain_rate' => Arr::get($data, 'drainRate'),
            'capacity' => Arr::get($data, 'capacity'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
