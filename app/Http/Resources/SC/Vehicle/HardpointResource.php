<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
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
            ref: '#/components/schemas/hardpoint_item_v2',
            type: 'object',
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

    public function toArray(Request $request): array
    {
        $hasItem = ! empty($this->equipped_item_uuid);

        if ($hasItem) {
            $this->load('item');
        }

        $data = [
            'name' => $this->hardpoint_name,
            'position' => $this->position,
            'min_size' => $this->min_size,
            'max_size' => $this->max_size,
            'class_name' => $this->class_name,
            'health' => $hasItem ? $this->item?->durabilityData?->health : null,
            'type' => $hasItem ? $this->item?->type : null,
            'sub_type' => $hasItem ? $this->item?->sub_type : null,
            $this->mergeWhen(...$this->addItem()),
            $this->mergeWhen($this->children !== null && $this->children->count() > 0, fn () => [
                'children' => self::collection($this->children),
            ]),
        ];

        if ($hasItem) {
            $data += [
                'type' => $this->item->type,
                'sub_type' => $this->item->sub_type,
            ];

            if ($this->min_size === 0) {
                $data['min_size'] = $this->item->size;
                $data['max_size'] = $this->item->size;
            }
        }

        return $data;
    }

    private function addItem(): array
    {
        if (empty($this->equipped_item_uuid)) {
            return [false, []];
        }

        if (
            $this->vehicleItem->exists ||
            ($this->item !== null && ($this->item->exists || $this->item->isTurret() || $this->item->type === 'Cargo'))
        ) {
            return [true, fn () => ['item' => new HardpointItemResource($this->item)]];
        }

        return [false, []];
    }
}
