<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class PowerPlant extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($item['PowerPlant'])) {
            return null;
        }

        return [
            'power_output' => $item['PowerPlant']['Output'],
        ];
    }
}
