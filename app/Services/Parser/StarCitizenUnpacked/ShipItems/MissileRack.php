<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class MissileRack extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (!isset($item['MissileRack'])) {
            return null;
        }

        return [
            'missile_count' => $item['MissileRack']['Count'] ?? null,
            'missile_size' => $item['MissileRack']['Size'] ?? null,
        ];
    }
}
