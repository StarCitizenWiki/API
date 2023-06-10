<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class Cooler extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'SCItemCoolerParams');

        if ($data === null) {
            return null;
        }

        return [
            'cooling_rate' => Arr::get($data, 'CoolingRate'),
            'suppression_ir_factor' => Arr::get($data, 'SuppressionIRFactor'),
            'suppression_heat_factor' => Arr::get($data, 'SuppressionHeatFactor'),
        ];
    }
}
