<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_link',
    title: 'Item Link',
    description: 'Link information to an Item',
    type: 'object',
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'uuid', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'class_name', type: 'string', example: '987_jacket_03_01_04'),
                new OA\Property(property: 'type', type: 'string'),
                new OA\Property(property: 'sub_type', type: 'string', nullable: true),
                new OA\Property(property: 'classification', type: 'string', example: 'FPS.Clothing.Torso', nullable: true),
                new OA\Property(property: 'is_base_variant', type: 'boolean'),
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
                new OA\Property(property: 'link', type: 'string'),
                new OA\Property(property: 'base_variant', description: 'Link to base variant item', type: 'string', nullable: true),
                new OA\Property(
                    property: 'variants',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link'),
                    nullable: true,
                ),
            ],
            type: 'object',
        ),
        new OA\Schema(ref: '#/components/schemas/metadata'),
    ]
)]
class ItemLinkResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $data = $this->data->first();

        return [
            'uuid' => $this->uuid,
            'name' => $data->name,
            'class_name' => $data->class_name,
            'type' => $data->type,
            'sub_type' => $data->sub_type,
            'classification' => $data->classification,
            'is_base_variant' => $data->base_id === null,
            'manufacturer' => new ManufacturerLinkResource($data->manufacturer),
            'link' => $this->makeApiUrl(self::ITEMS_SHOW, $this->uuid),
            $this->mergeWhen($data->base_id !== null, fn () => [
                'base_variant' => $this->makeApiUrl(self::ITEMS_SHOW, $data->baseVariant->uuid ?? ''),
            ]),
            'variants' => self::collection($this->whenLoaded('variants')),
            'shops' => [],

            'updated_at' => $this->updated_at,
            'version' => $data->gameVersion->code,
        ];
    }
}
