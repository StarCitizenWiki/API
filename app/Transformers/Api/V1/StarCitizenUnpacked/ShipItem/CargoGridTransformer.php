<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\CargoGrid;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class CargoGridTransformer extends AbstractCommodityTransformer
{
    public function transform(CargoGrid $item): array
    {
        return [
            'personal_inventory' => $item->personal_inventory,
            'invisible' => $item->invisible,
            'mining_only' => $item->mining_only,
            'min_volatile_power_to_explode' => $item->min_volatile_power_to_explode,
            'scu' => $item->scu,
            'dimension' => $item->dimension,
            'dimensions' => [
                'x' => $item->x,
                'y' => $item->y,
                'z' => $item->z,
            ],
        ];
    }
}
