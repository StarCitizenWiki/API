<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\Thruster;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class ThrusterTransformer extends AbstractCommodityTransformer
{
    public function transform(Thruster $item): array
    {
        return [
            'thrust_capacity' => $item->thrust_capacity,
            'min_health_thrust_multiplier' => $item->min_health_thrust_multiplier,
            'fuel_burn_per_10k_newton' => $item->fuel_burn_per_10k_newton,
            'type' => $item->type,
        ];
    }
}
