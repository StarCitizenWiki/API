<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'hardpoint_v3',
    title: 'Vehicle Port',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'sizes', properties: [
            new OA\Property(property: 'min', type: 'integer', nullable: true),
            new OA\Property(property: 'max', type: 'integer', nullable: true),
        ], type: 'object'),
        new OA\Property(property: 'class_name', type: 'string', nullable: true),
        new OA\Property(property: 'health', type: 'double', nullable: true),
        new OA\Property(property: 'compatible_types', ref: '#/components/schemas/item_port_type_v2', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type', type: 'double', nullable: true),
        new OA\Property(
            property: 'ports',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/hardpoint_v3'),
            nullable: true
        ),
        new OA\Property(
            property: 'equipped_item',
            ref: '#/components/schemas/hardpoint_item_v3',
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class HardpointResourceV3 extends AbstractBaseResource
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
            'sizes' => [
                'min' => $this->min_size,
                'max' => $this->max_size,
            ],
            'class_name' => $this->class_name,
            'health' => $hasItem ? $this->item?->durabilityData?->health : null,
            'compatible_types' => $hasItem ? array_filter([
                array_filter([
                    'type' => $this->item?->type,
                    'sub_types' => array_filter([$this->item?->sub_type]),
                ]),
            ]) : null,
            $this->mergeWhen(...$this->addItem()),
            $this->mergeWhen($this->children !== null && $this->children->count() > 0, fn () => [
                'ports' => self::collection($this->children),
            ]),
        ];

        if ($hasItem && $this->min_size === 0) {
            $data['sizes']['min'] = $this->item->size;
            $data['sizes']['max'] = $this->item->size;
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
            return [true, fn () => ['equipped_item' => new HardpointItemResourceV3($this->item)]];
        }

        return [false, []];
    }
}
