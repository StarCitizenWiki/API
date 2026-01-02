<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\Game\Item\ItemInventoryResource;
use App\Http\Resources\Game\Item\ItemPortResource;
use App\Http\Resources\Game\Item\ItemResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_hardpoint_item',
    title: 'Vehicle Hardpoint Item v2',
    description: 'Deprecated: Trimmed down version of item resource, used for hardpoints in v2 API.',
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
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
                new OA\Property(property: 'type', type: 'string', nullable: true),
                new OA\Property(property: 'sub_type', type: 'string', nullable: true),
                new OA\Property(property: 'inventory', ref: '#/components/schemas/item_container', nullable: true),
                new OA\Property(
                    property: 'ports',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_port'),
                    nullable: true
                ),
            ],
            type: 'object'
        ),
    ],
)]
class HardpointItemResource extends ItemResource
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

        $itemData = $this->data->first();

        if ($itemData === null) {
            return [];
        }

        return [
            'uuid' => $this->uuid,
            'name' => $itemData->name,
            'class_name' => $itemData->class_name,
            'link' => $this->makeApiUrl(self::ITEMS_SHOW, $this->uuid),
            'size' => $itemData->size,
            'mass' => $this->extractNumeric($itemData, 'Mass'),
            'grade' => match ($itemData->grade) {
                1 => 'A',
                2 => 'B',
                3 => 'C',
                4 => 'D',
                default => $itemData->grade,
            },
            'class' => $itemData->class,
            'manufacturer' => new ManufacturerLinkResource($itemData->manufacturer),
            'type' => str_replace('NOITEM_', '', ($itemData->type ?? '')),
            'sub_type' => $itemData->sub_type,
            $this->mergeWhen($this->isTurret($itemData), fn () => $this->addTurretData($itemData)),
            $this->mergeWhen(...$this->addSpecification($this->resource, $itemData)),
            $this->mergeWhen($this->hasInStdItem($itemData, 'InventoryContainer'), [
                'inventory' => new ItemInventoryResource($this->extractFromStdItem($itemData, 'InventoryContainer')),
            ]),
            'ports' => ItemPortResource::collection($this->when($this->hasInStdItem($itemData, 'Ports'), $this->extractPorts($itemData))),
            'updated_at' => $this->updated_at,
            'version' => $itemData->gameVersion->code,
        ];
    }
}
