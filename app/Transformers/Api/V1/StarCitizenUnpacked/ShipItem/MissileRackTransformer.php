<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\Weapon\MissileRack;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class MissileRackTransformer extends AbstractCommodityTransformer
{

    public function transform(MissileRack $item): array
    {
        return [
            'missile_size' => $item->missile_size,
            'missile_count' => $item->missile_count,
        ];
    }
}
