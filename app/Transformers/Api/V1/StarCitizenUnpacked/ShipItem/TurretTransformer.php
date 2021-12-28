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
            'max_mounts' => $item->max_mounts ?? '-',
            'min_size' => $item->min_size ?? '-',
            'max_size' => $item->max_size ?? '-',
        ];
    }
}
