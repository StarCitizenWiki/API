<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class Radar extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemRadarComponentParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'detection_lifetime' => Arr::get($data, 'detectionLifetime'),
            'altitude_ceiling' => Arr::get($data, 'altitudeCeiling'),
            'enable_cross_section_occlusion' => Arr::get($data, 'enableCrossSectionOcclusion'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
