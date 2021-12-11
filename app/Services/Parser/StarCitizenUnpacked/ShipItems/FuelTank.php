<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class FuelTank extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SCItemFuelTankParams'])) {
            return null;
        }

        $basePath = 'Components.SCItemFuelTankParams.';

        return array_filter([
            'fill_rate' => $rawData->pull($basePath . 'fillRate'),
            'drain_rate' => $rawData->pull($basePath . 'drainRate'),
            'capacity' => $rawData->pull($basePath . 'capacity'),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
