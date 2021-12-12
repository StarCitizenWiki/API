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

        if ($hardpoint->item !== null && $hardpoint->item->specification !== null) {
            $this->defaultIncludes[] = 'item';
        }

        $this->defaultIncludes[] = 'children';

        return $data;
    }

    public function includeItem(VehicleHardpoint $item)
    {
        if ($item->item === null) {
            return $this->null();
        }

        return $this->item($item->item->specification, new ShipItemTransformer());
    }

    public function includeChildren(VehicleHardpoint $hardpoint): Collection
    {
        return $this->collection($hardpoint->children2, new self());
    }
}
