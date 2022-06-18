<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\FuelTank;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class FuelTankTransformer extends AbstractCommodityTransformer
{
    public function transform(FuelTank $item): array
    {
        return [
            'fill_rate' => $item->fill_rate,
            'drain_rate' => $item->drain_rate,
            'capacity' => $item->capacity,
        ];
    }
}
