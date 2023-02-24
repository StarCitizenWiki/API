<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class FuelIntake extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SCItemFuelIntakeParams'])) {
            return null;
        }

        $basePath = 'Components.SCItemFuelIntakeParams.';

        return array_filter([
            'fuel_push_rate' => $rawData->pull($basePath . 'fuelPushRate'),
            'minimum_rate' => $rawData->pull($basePath . 'minimumRate'),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
