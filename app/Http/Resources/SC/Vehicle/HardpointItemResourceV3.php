<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\SC\Item\ItemContainerResource;
use App\Http\Resources\SC\Item\ItemPortResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Http\Resources\SC\Manufacturer\ManufacturerLinkResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'hardpoint_item_v3',
    title: 'Hardpoint Item',
    description: 'Trimmed down version of schema item_v2, used on hardpoints.',
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'uuid', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'class_name', type: 'string'),
                new OA\Property(property: 'link', type: 'string'),
                new OA\Property(property: 'size', type: 'integer', nullable: true),
                new OA\Property(property: 'mass', type: 'double', nullable: true),
                new OA\Property(property: 'grade', type: 'string', nullable: true),
                new OA\Property(property: 'class', type: 'string', nullable: true),
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link_v2'),
                new OA\Property(property: 'type', type: 'string', nullable: true),
                new OA\Property(property: 'sub_type', type: 'string', nullable: true),
                new OA\Property(property: 'inventory', ref: '#/components/schemas/item_container_v2', nullable: true),
                new OA\Property(property: 'ports', ref: '#/components/schemas/item_port_data_v2', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Schema(ref: '#/components/schemas/vehicle_item_specification_v2'),
        new OA\Schema(ref: '#/components/schemas/metadata_v2'),
    ],
)]

class HardpointItemResourceV3 extends ItemResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        if ($this->uuid === null) {
            return [];
        }

        $vehicleItem = $this->vehicleItem;

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'class_name' => $this->class_name,
            'link' => $this->makeApiUrl(self::ITEMS_SHOW, $this->uuid),
            'size' => $this->size,
            'mass' => $this->mass,
            'grade' => $vehicleItem->grade,
            'class' => $vehicleItem->class,
            'manufacturer' => new ManufacturerLinkResource($this->manufacturer),
            'type' => $this->cleanType(),
            'sub_type' => $this->sub_type,
            $this->mergeWhen($this->isTurret(), fn () => $this->addTurretData()),
            $this->mergeWhen(...$this->addSpecification()),
            $this->mergeWhen($this->container->exists, fn () => [
                'inventory' => new ItemContainerResource($this->container),
            ]),
            'ports' => ItemPortResource::collection($this->whenLoaded('ports')),
            'updated_at' => $this->updated_at,
            'version' => $this->version,
        ];
    }
}
