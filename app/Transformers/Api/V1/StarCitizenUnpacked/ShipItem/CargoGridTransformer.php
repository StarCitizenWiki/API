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
