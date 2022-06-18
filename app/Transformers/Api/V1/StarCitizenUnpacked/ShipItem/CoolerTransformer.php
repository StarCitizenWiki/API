<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\Cooler;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class CoolerTransformer extends AbstractCommodityTransformer
{
    public function transform(Cooler $item): array
    {
        return [
            'cooling_rate' => $item->cooling_rate,
            'suppression_ir_factor' => $item->suppression_ir_factor,
            'suppression_heat_factor' => $item->suppression_heat_factor,
        ];
    }
}
