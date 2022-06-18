<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\CounterMeasure;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class CounterMeasureTransformer extends AbstractCommodityTransformer
{
    public function transform(CounterMeasure $item): array
    {
        return [
            'initial_ammo_count' => $item->initial_ammo_count,
            'max_ammo_count' => $item->max_ammo_count,
        ];
    }
}
