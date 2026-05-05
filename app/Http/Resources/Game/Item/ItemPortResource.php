<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_port',
    title: 'Item Port',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'display_name', type: 'string', nullable: true),
        new OA\Property(property: 'position', type: 'string', nullable: true),
        new OA\Property(property: 'size', type: 'integer', nullable: true),
        new OA\Property(property: 'sizes', properties: [
            new OA\Property(property: 'min', type: 'integer', nullable: true),
            new OA\Property(property: 'max', type: 'integer', nullable: true),
        ], type: 'object'),
        new OA\Property(
            property: 'compatible_types',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/item_port_type'),
            nullable: true
        ),
        new OA\Property(
            property: 'types',
            description: 'Raw type strings as provided by the game data',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(
            property: 'required_tags',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(
            property: 'flags',
            description: 'Port flags from game data',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(
            property: 'editable',
            description: 'Whether the port can be modified in-game.',
            type: 'boolean',
            nullable: true,
        ),
        new OA\Property(
            property: 'uneditable',
            description: 'Deprecated: Use editable (inverted value).',
            type: 'boolean',
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'equipped_item_uuid',
            description: 'UUID of the equipped item',
            type: 'string',
            nullable: true,
        ),
        new OA\Property(property: 'equipped_item', ref: '#/components/schemas/item_link', nullable: true),
        new OA\Property(property: 'equipped_port_item', ref: '#/components/schemas/game_port_item', nullable: true),
    ],
    type: 'object'
)]
class ItemPortResource extends AbstractBaseResource
{
    use ResolvesGameVersion;

    public function toArray(Request $request): array
    {
        $itemData = null;
        if (Arr::has($this, 'EquippedItem') && Arr::get($this, 'EquippedItem') !== null && $request->routeIs('items.show')) {
            $itemData = $this->loadItemDataForVersion(Arr::get($this, 'EquippedItem'));
        }

        return [
            'name' => Arr::get($this, 'PortName'),
            'display_name' => Arr::get($this, 'DisplayName'),
            'position' => Arr::get($this, 'Position', strtoupper(Arr::get($this, 'PortName', ''))),
            'size' => Arr::get($this, 'Size'),
            'sizes' => [
                'min' => Arr::get($this, 'MinSize'),
                'max' => Arr::get($this, 'MaxSize'),
            ],
            'compatible_types' => Arr::has($this, 'CompatibleTypes') && is_array(Arr::get($this, 'CompatibleTypes'))
                ? ItemPortTypeResource::collection(Arr::get($this, 'CompatibleTypes'))
                : null,
            'types' => Arr::get($this, 'Types', []),
            'tags' => Arr::get($this, 'Tags', []),
            'required_tags' => Arr::get($this, 'RequiredTags', []),
            'flags' => Arr::get($this, 'Flags', []),
            'editable' => ! Arr::get($this, 'Uneditable'),
            'uneditable' => Arr::get($this, 'Uneditable'),
            'equipped_item_uuid' => Arr::get($this, 'EquippedItem'),
            $this->mergeWhen($itemData !== null, [
                'equipped_item' => new ItemLinkResource($itemData),
            ]),
            // $this->mergeWhen($itemData !== null, fn () => [
            //     'equipped_port_item' => new PortItemResource($itemData->item),
            // ]),
        ];
    }
}
