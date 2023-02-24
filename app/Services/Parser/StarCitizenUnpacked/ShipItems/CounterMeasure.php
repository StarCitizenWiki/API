<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class CounterMeasure extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SAmmoContainerComponentParams'])) {
            return null;
        }

        $basePath = 'Components.SAmmoContainerComponentParams.';

        return array_filter([
            'initial_ammo_count' => $rawData->pull($basePath . 'initialAmmoCount'),
            'max_ammo_count' => $rawData->pull($basePath . 'maxAmmoCount'),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
