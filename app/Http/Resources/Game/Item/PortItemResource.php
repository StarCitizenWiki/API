<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Models\Game\ItemData;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_port_item',
    title: 'Equipped Port Item',
    description: 'Item equipped in a port, with basic item data, optional manufacturer, and conditional resource network and emission details.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique identifier of the item.', type: 'string'),
        new OA\Property(property: 'name', description: 'Display name of the item.', type: 'string'),
        new OA\Property(property: 'class_name', description: 'Internal class name of the item definition.', type: 'string'),
        new OA\Property(property: 'type', description: 'AttachDef@Type (NOITEM_ prefix removed)', type: 'string', nullable: true),
        new OA\Property(property: 'type_label', description: 'Human-readable label for the item type', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type', description: 'AttachDef@SubType', type: 'string', nullable: true),
        new OA\Property(property: 'sub_type_label', description: 'Human-readable label for the item sub-type', type: 'string', nullable: true),
        new OA\Property(property: 'classification', description: 'Dot-separated classification path (e.g. FPS.Clothing.Torso).', type: 'string', example: 'FPS.Clothing.Torso', nullable: true),
        new OA\Property(property: 'classification_label', description: 'Human-readable label for the item classification', type: 'string', nullable: true),
        new OA\Property(property: 'is_base_variant', description: 'Whether this item is the base variant (has no parent variant).', type: 'boolean'),
        new OA\Property(property: 'variant_name', description: 'Extracted variant name, e.g. "Executive Edition" or "Aqua"', type: 'string', nullable: true),
        new OA\Property(property: 'link', description: 'API URL for item detail endpoint', type: 'string'),
        new OA\Property(property: 'web_url', description: 'Web URL for item detail page', type: 'string', nullable: true),
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
            nullable: true,
            x: ['suffix' => ' kg']
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
            'type' => $this->stripItemTypePrefix($itemData->type),
            'type_label' => $itemData->type_label,
            'sub_type' => $itemData->sub_type,
            'sub_type_label' => $itemData->sub_type_label,
            'classification' => $itemData->classification,
            'classification_label' => $itemData->classification_label,
            'is_base_variant' => $itemData->base_id === null,
            'variant_name' => $itemData->relationLoaded('variantGroupItem') && $itemData->variantGroupItem !== null
                ? $itemData->variantGroupItem->variant_name
                : null,
            'link' => $this->urlWithVersion(route('items.show', ['identifier' => $this->uuid]), $request),
            'web_url' => $this->urlWithVersion(route('web.items.show', ['item' => $this->slug ?? $this->uuid]), $request),
            'size' => $itemData->size,
            'mass' => $itemData->mass,
            'grade' => ItemData::formatGrade($itemData->grade, $itemData->classification)
                ?? $this->extractFromStdItem($itemData, 'DescriptionData.Grade')
                ?? $itemData->grade,
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
