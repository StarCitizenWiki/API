<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class SelfDestruct extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SSCItemSelfDestructComponentParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'damage' => Arr::get($data, 'damage'),
            'radius' => Arr::get($data, 'radius'),
            'min_radius' => Arr::get($data, 'minRadius'),
            'phys_radius' => Arr::get($data, 'physRadius'),
            'min_phys_radius' => Arr::get($data, 'minPhysRadius'),
            'time' => Arr::get($data, 'time'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
