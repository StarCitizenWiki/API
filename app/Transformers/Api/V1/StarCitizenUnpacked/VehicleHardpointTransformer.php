<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\VehicleHardpoint;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipItemTransformer;
use League\Fractal\Resource\Collection;

class VehicleHardpointTransformer extends AbstractCommodityTransformer
{

    public function transform(VehicleHardpoint $hardpoint): array
    {
        $data = [
            'name' => $hardpoint->hardpoint->name,
        ];

        if ($hardpoint->item !== null && $hardpoint->item->exists()) {
            $this->defaultIncludes[] = 'item';
        }

        if ($hardpoint->children->isNotEmpty()) {
            $this->defaultIncludes[] = 'children';
        }

        return $data;
    }

    public function includeItem(VehicleHardpoint $item): \League\Fractal\Resource\Item
    {
        return $this->item($item->item->itemSpecification, new ShipItemTransformer());
    }

    public function includeChildren($hardpoint): Collection
    {
        return $this->collection($hardpoint->children, new self());
    }
}
