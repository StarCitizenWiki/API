<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\FuelIntake;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class FuelIntakeTransformer extends AbstractCommodityTransformer
{
    public function transform(FuelIntake $item): array
    {
        return [
            'fuel_push_rate' => $item->fuel_push_rate,
            'minimum_rate' => $item->minimum_rate,
        ];
    }
}
