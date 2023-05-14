<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'hardpoint_v2',
    title: 'Hardpoints',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'min_size', type: 'string', nullable: true),
        new OA\Property(property: 'max_size', type: 'string', nullable: true),
        new OA\Property(property: 'class_name', type: 'string', nullable: true),
        new OA\Property(property: 'health', type: 'double', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type', type: 'double', nullable: true),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/hardpoint_v2'),
            nullable: true
        ),
        new OA\Property(
            property: 'item',
            ref: '#/components/schemas/item_v2',
            type: 'double',
            nullable: true
        ),
    ],
    type: 'object'
)]
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
    public function toArray($request): array
    {
        $data = [
            'name' => $this->hardpoint_name,
            'min_size' => $this->min_size,
            'max_size' => $this->max_size,
            'class_name' => $this->class_name,
            'health' => $this->item?->durabilityData?->health,
            'type' => $this->item?->type,
            'sub_type' => $this->item?->sub_type,
            $this->mergeWhen(...$this->addItem()),
            $this->mergeWhen($this->children->count() > 0, [
                'children' => self::collection($this->children),
            ]),
        ];

        if ($this->item?->uuid !== null) {
            $data += [
                'type' => $this->item->type,
                'sub_type' => $this->item->sub_type,
            ];

            if ($this->min_size ===  0) {
                $data['min_size'] = $this->item->size;
                $data['max_size'] = $this->item->size;
            }
        }

        return $data;
    }

    private function addItem(): array
    {
        if ($this->vehicleItem->exists) {
            return [true, ['item' => new ItemResource($this->item, true)]];
        }

        if ($this->item !== null && ($this->item->isTurret() || $this->item->type === 'Cargo')) {
            return [true, ['item' => new ItemResource($this->item, true)]];

        }

        if ($this->item !== null && $this->item->exists) {
            return [true, ['item' => new ItemResource($this->item)]];
        }

        return [false, []];
    }
}
