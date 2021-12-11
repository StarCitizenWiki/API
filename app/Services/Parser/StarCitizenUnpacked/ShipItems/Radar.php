<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class Radar extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($rawData['Components']['SCItemRadarComponentParams'])) {
            return null;
        }

        $basePath = 'Components.SCItemRadarComponentParams.';

        return array_filter([
            'detection_lifetime' => $rawData->pull($basePath . 'detectionLifetime'),
            'altitude_ceiling' => $rawData->pull($basePath . 'altitudeCeiling'),
            'enable_cross_section_occlusion' => $rawData->pull($basePath . 'enableCrossSectionOcclusion'),
        ], static function ($entry) {
            return !empty($entry);
        });
    }
}
