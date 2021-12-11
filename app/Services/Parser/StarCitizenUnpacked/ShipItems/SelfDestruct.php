<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class SelfDestruct extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SSCItemSelfDestructComponentParams'])) {
            return null;
        }

        $basePath = 'Components.SSCItemSelfDestructComponentParams.';

        return array_filter([
            'damage' => $rawData->pull($basePath . 'damage'),
            'radius' => $rawData->pull($basePath . 'radius'),
            'min_radius' => $rawData->pull($basePath . 'minRadius'),
            'phys_radius' => $rawData->pull($basePath . 'physRadius'),
            'min_phys_radius' => $rawData->pull($basePath . 'minPhysRadius'),
            'time' => $rawData->pull($basePath . 'time'),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
