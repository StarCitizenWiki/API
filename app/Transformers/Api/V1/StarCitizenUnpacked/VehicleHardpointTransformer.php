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
        $this->defaultIncludes = [];

        $data = [
            'name' => $hardpoint->hardpoint_name,
            'min_size' => $hardpoint->min_size,
            'max_size' => $hardpoint->max_size,
            'class_name' => $hardpoint->class_name,
        ];

        if ($hardpoint->item !== null && $hardpoint->item->specification !== null) {
            $data += [
                'type' => $hardpoint->item->item->type,
                'sub_type' => $hardpoint->item->item->sub_type,
            ];

            $this->defaultIncludes[] = 'item';
        }

        $data = array_filter($data);

        $this->defaultIncludes[] = 'children';

        return $data;
    }

    public function includeItem(VehicleHardpoint $item)
    {
        if ($item->item === null) {
            return $this->null();
        }

        $transformer = new ShipItemTransformer();
        $transformer->excludeDefaults();

        return $this->item($item->item->specification, $transformer);
    }

    public function includeChildren(VehicleHardpoint $hardpoint): Collection
    {
        return $this->collection($hardpoint->children2, new self());
    }
}
