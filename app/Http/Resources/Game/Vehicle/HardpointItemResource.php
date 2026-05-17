<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\Game\Item\ItemInventoryResource;
use App\Http\Resources\Game\Item\ItemPortResource;
use App\Http\Resources\Game\Item\ItemResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Models\Game\ItemData;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_hardpoint_item',
    title: 'Vehicle Hardpoint Item v2',
    description: 'Deprecated: Trimmed down version of item resource, used for hardpoints in v2 API.',
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'uuid', description: 'Unique item identifier.', type: 'string'),
                new OA\Property(property: 'name', description: 'Display name of the equipped item.', type: 'string'),
                new OA\Property(property: 'class_name', description: 'SC item class name (e.g. BEHR_LaserCannon_S4).', type: 'string'),
                new OA\Property(property: 'link', description: 'API URL for the full item detail.', type: 'string'),
                new OA\Property(property: 'size', description: 'Item size as integer.', type: 'integer', nullable: true),
                new OA\Property(property: 'mass', description: 'Item mass in kg.', type: 'double', nullable: true),
                new OA\Property(property: 'grade', description: 'Item grade letter (A-D).', type: 'string', nullable: true),
                new OA\Property(property: 'class', description: 'Item class name from game data.', type: 'string', nullable: true),
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
                new OA\Property(property: 'type', description: 'Item type with NOITEM_ prefix stripped.', type: 'string', nullable: true),
                new OA\Property(property: 'type_label', description: 'Human-readable label for the item type.', type: 'string', nullable: true),
                new OA\Property(property: 'sub_type', description: 'Item sub-type identifier.', type: 'string', nullable: true),
                new OA\Property(property: 'sub_type_label', description: 'Human-readable label for the item sub-type.', type: 'string', nullable: true),
                new OA\Property(property: 'inventory', ref: '#/components/schemas/item_inventory', description: 'Inventory container data, if the item has one.', nullable: true),
                new OA\Property(
                    property: 'ports',
                    description: 'Sub-ports available on this item.',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_port'),
                    nullable: true
                ),
                new OA\Property(property: 'updated_at', description: 'Timestamp of the last update.', type: 'string', format: 'date-time', nullable: true),
                new OA\Property(property: 'version', description: 'Game version code this item belongs to.', type: 'string', example: '4.4.0-LIVE.12340123', nullable: true),
            ],
            type: 'object'
        ),
    ],
)]
class HardpointItemResource extends ItemResource
{
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
            'link' => route('items.show', ['identifier' => $this->uuid]),
            'size' => $itemData->size,
            'mass' => $this->extractNumeric($itemData, 'Mass'),
            'grade' => ItemData::formatGrade($itemData->grade, $itemData->classification),
            'class' => $itemData->class,
            'manufacturer' => new ManufacturerLinkResource($itemData->manufacturer),
            'type' => $this->stripItemTypePrefix($itemData->type),
            'type_label' => $itemData->type_label,
            'sub_type' => $itemData->sub_type,
            'sub_type_label' => $itemData->sub_type_label,
            $this->mergeWhen($this->isTurret($itemData), fn () => $this->addTurretData($itemData)),
            $this->mergeWhen(...$this->addSpecification($itemData)),
            $this->mergeWhen($this->hasInStdItem($itemData, 'InventoryContainer'), [
                'inventory' => new ItemInventoryResource($this->extractFromStdItem($itemData, 'InventoryContainer')),
            ]),
            'ports' => ItemPortResource::collection($this->when($this->hasInStdItem($itemData, 'Ports'), $this->extractPorts($itemData))),
            'updated_at' => $this->updated_at,
            'version' => $itemData->gameVersion->code,
        ];
    }
}
