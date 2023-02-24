<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class Cooler extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($item['Cooler'])) {
            return null;
        }

        return [
            'cooling_rate' => $item['Cooler']['Rate'],
            'suppression_ir_factor' => $rawData->pull('Components.SCItemCoolerParams.SuppressionIRFactor'),
            'suppression_heat_factor' => $rawData->pull('Components.SCItemCoolerParams.SuppressionHeatFactor'),
        ];
    }
}
