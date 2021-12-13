<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\Hardpoint;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipItemTransformer;
use League\Fractal\Resource\Collection;

class HardpointTransformer extends AbstractCommodityTransformer
{
    public function transform(Hardpoint $hardpoint): array
    {
        $this->defaultIncludes = [];

        $data = [
            'name' => $hardpoint->name,
            'min_size' => $hardpoint->hardpoint_data->min_size,
            'max_size' => $hardpoint->hardpoint_data->max_size,
            'class_name' => $hardpoint->hardpoint_data->class_name,
        ];

        if ($hardpoint->hardpoint_data->item !== null) {
            $data += [
                'type' => $hardpoint->hardpoint_data->item->item->type,
                'sub_type' => $hardpoint->hardpoint_data->item->item->sub_type,
            ];
        }

        if ($hardpoint->hardpoint_data->equipped_vehicle_item_uuid !== null) {
            $this->defaultIncludes[] = 'item';
        }

        if ($hardpoint->hardpoint_data->children->isNotEmpty()) {
            $this->defaultIncludes[] = 'children';
        }

        return $data;
    }

    public function includeChildren($hardpoint): Collection
    {
        return $this->collection($hardpoint->hardpoint_data->children, new VehicleHardpointTransformer());
    }

    public function includeItem(Hardpoint $item)
    {
        if ($item->hardpoint_data->item === null) {
            return $this->null();
        }

        $transformer = new ShipItemTransformer();
        $transformer->excludeDefaults();

        return $this->item($item->hardpoint_data->item->specification, $transformer);
    }
}
