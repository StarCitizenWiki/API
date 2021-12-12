<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\VehicleHardpoint;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipItemTransformer;

class VehicleHardpointTransformer extends AbstractCommodityTransformer
{

    public function transform(VehicleHardpoint $hardpoint): array
    {
        $data = [
            'name' => $hardpoint->hardpoint->name,
        ];

        if ($hardpoint->item !== null && $hardpoint->item->specification !== null) {
            $data['item'] = $this->item($hardpoint->item->specification, new ShipItemTransformer());
        }

        if ($hardpoint->children !== null && $hardpoint->children->isNotEmpty()) {
            $data['children'] = $this->collection($hardpoint->children, new self());
        }

        return $data;
    }
}
