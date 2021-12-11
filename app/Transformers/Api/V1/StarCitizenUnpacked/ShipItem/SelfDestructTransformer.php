<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\SelfDestruct;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class SelfDestructTransformer extends AbstractCommodityTransformer
{

    public function transform(SelfDestruct $item): array
    {
        return [
            'damage' => $item->damage,
            'radius' => $item->radius,
            'min_radius' => $item->min_radius,
            'phys_radius' => $item->phys_radius,
            'min_phys_radius' => $item->min_phys_radius,
            'time' => $item->time,
        ];
    }
}
