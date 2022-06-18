<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\Turret;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class TurretTransformer extends AbstractCommodityTransformer
{
    public function transform($item): array
    {
        return [
            'max_mounts' => $item->max_mounts ?? 0,
            'min_size' => $item->min_size ?? 0,
            'max_size' => $item->max_size ?? 0,
        ];
    }
}
