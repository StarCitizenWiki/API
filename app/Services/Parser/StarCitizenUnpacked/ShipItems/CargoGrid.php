<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class CargoGrid extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SCItemCargoGridParams'])) {
            return null;
        }

        $basePath = 'Components.SCItemCargoGridParams.';

        return array_filter([
            'personal_inventory' => $rawData->pull($basePath . 'personalInventory'),
            'invisible' => $rawData->pull($basePath . 'invisible'),
            'mining_only' => $rawData->pull($basePath . 'miningOnly'),
            'min_volatile_power_to_explode' => $rawData->pull($basePath . 'minVolatilePowerToExplode'),
            'x' => $rawData->pull($basePath . 'dimensions.x'),
            'y' => $rawData->pull($basePath . 'dimensions.y'),
            'z' => $rawData->pull($basePath . 'dimensions.z'),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
