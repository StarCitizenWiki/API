<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\ShipItem\PowerPlant;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class ShipPowerPlantTransformer extends AbstractCommodityTransformer
{
    public function transform(PowerPlant $item): array
    {
        return [
            'power_output' => $item->power_output,
        ];
    }
}
