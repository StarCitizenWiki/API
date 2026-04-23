<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_port_item',
    title: 'Equipped Port Item',
    description: 'Item equipped in a port, with basic item data, optional manufacturer, and conditional resource network and emission details.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'class_name', type: 'string'),
        new OA\Property(property: 'type', description: 'AttachDef@Type (NOITEM_ prefix removed)', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type', description: 'AttachDef@SubType', type: 'string', nullable: true),
        new OA\Property(property: 'link', description: 'API URL for item detail endpoint', type: 'string'),
        new OA\Property(
            property: 'size',
            description: 'AttachDef@Size',
            type: 'integer',
            nullable: true
        ),
        new OA\Property(
            property: 'mass',
            description: 'Generated from SEntityRigidPhysicsControllerParams',
            type: 'double',
            nullable: true
        ),
        new OA\Property(
            property: 'grade',
            description: 'Derived formatting for ship grades (A, B, C, D) or extracted from DescriptionData.Grade',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'class',
            description: 'From DescriptionData.Class if not set directly',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'manufacturer',
            ref: '#/components/schemas/manufacturer_link',
            description: 'Present when manufacturer is set',
            nullable: true
        ),
        new OA\Property(
            property: 'resource_network',
            ref: '#/components/schemas/resource_network',
            description: 'Present when stdItem.ResourceNetwork exists',
            nullable: true
        ),
        new OA\Property(
            property: 'emission',
            ref: '#/components/schemas/item_emission',
            description: 'Present when stdItem.ResourceNetwork exists; calculated emission values',
            nullable: true
        ),
        new OA\Property(
            property: 'version',
            description: 'Game version code',
            type: 'string',
            example: '4.4.0-LIVE.12340123'
        ),
    ],
    type: 'object'
)]
class PortItemResource extends ItemResource
{
    public function toArray(Request $request): array
    {
        $itemData = $this->data?->first();

        if ($itemData === null) {
            return [];
        }

        return [
            'uuid' => $this->uuid,
            'name' => $itemData->name,
            'class_name' => $itemData->class_name,
            'type' => $itemData->type,
            'sub_type' => $itemData->sub_type,
            'link' => route('items.show', ['identifier' => $this->uuid]),
            'size' => $itemData->size,
            'mass' => $this->extractFromStdItem($itemData, 'Mass'),
            'grade' => match ($itemData->grade) {
                1 => 'A',
                2 => 'B',
                3 => 'C',
                4 => 'D',
                default => $this->extractFromStdItem($itemData, 'DescriptionData.Grade') ?? $itemData->grade,
            },
            'class' => $itemData->class ?? $this->extractFromStdItem($itemData, 'DescriptionData.Class'),

            $this->mergeWhen($itemData->manufacturer !== null, [
                'manufacturer' => new ManufacturerLinkResource($itemData->manufacturer),
            ]),
            $this->mergeWhen(...$this->addSpecification($itemData)),

            $this->mergeWhen($this->hasInStdItem($itemData, 'ResourceNetwork'), [
                'resource_network' => new ResourceNetworkResource($itemData),
                'emission' => new ItemEmissionResource($this->extractFromStdItem($itemData, 'Emission')),
            ]),

            'version' => $itemData->gameVersion->code,
        ];
    }
}
