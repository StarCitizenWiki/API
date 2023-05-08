<?php

declare(strict_types=1);

namespace App\Http\Resources\SC;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Http\Resources\SC\Vehicle\VehicleItemResource;
use Illuminate\Http\Request;

class HardpointResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'effects',
            'item.shops',
            'item.shops.items',
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'name' => $this->hardpoint_name,
            'min_size' => $this->min_size,
            'max_size' => $this->max_size,
            'class_name' => $this->class_name,
            'health' => optional($this->item->durabilityData)->health,
            $this->mergeWhen($this->children->count() > 0, [
                'children' => self::collection($this->children),
            ]),
            $this->mergeWhen(...$this->addItem()),
        ];


        if ($this->item->uuid !== null) {
            $data += [
                'type' => $this->item->type,
                'sub_type' => $this->item->sub_type,
            ];
        }

        return $data;
    }

    private function addItem(): array
    {
        if ($this->vehicleItem->exists) {
            return [true, ['item' => new VehicleItemResource($this->vehicleItem)]];
        }

        if ($this->item !== null && $this->item->exists) {
            return [true, ['item' => new ItemResource($this->item)]];
        }

        return [false, []];
    }
}
