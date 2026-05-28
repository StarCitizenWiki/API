<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Models\Game\ItemData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_port',
    title: 'Item Port',
    properties: [
        new OA\Property(property: 'name', description: 'Internal port identifier (e.g. hardpoint_weapon_wing_right).', type: 'string'),
        new OA\Property(property: 'display_name', description: 'Human-readable port name, resolved from localization key.', type: 'string', nullable: true),
        new OA\Property(property: 'position', description: 'Derived position label (e.g. magazine_well, optics, underbarrel, barrel).', type: 'string', nullable: true),
        new OA\Property(property: 'size', description: 'Maximum item size this port accepts (same as sizes.max).', type: 'integer', nullable: true),
        new OA\Property(property: 'sizes', description: 'Size range of items this port accepts.', properties: [
            new OA\Property(property: 'min', description: 'Minimum item size.', type: 'integer', nullable: true),
            new OA\Property(property: 'max', description: 'Maximum item size.', type: 'integer', nullable: true),
        ], type: 'object'),
        new OA\Property(
            property: 'compatible_types',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/item_port_type'),
            nullable: true
        ),
        new OA\Property(
            property: 'types',
            description: 'Structured compatible type entries with type and sub-types.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(
            property: 'tags',
            description: 'Tags provided by this port to attached items (from PortTags attribute).',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(
            property: 'required_tags',
            description: 'Tags an item must have to attach to this port (from RequiredPortTags, $ prefix stripped).',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(
            property: 'flags',
            description: 'Port flags controlling behavior (e.g. editable, uneditable, invisible, select).',
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
        new OA\Property(
            property: 'type',
            description: 'Port type derived from CompatibleTypes (e.g. WeaponGun, MissileLauncher).',
            type: 'string',
            nullable: true,
        ),
        new OA\Property(
            property: 'sub_type',
            description: 'Port sub-type derived from CompatibleTypes.',
            type: 'string',
            nullable: true,
        ),
        new OA\Property(
            property: 'port_tags',
            description: 'Tags provided by this port. Same as tags field, for vehicle port_tags compat.',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(
            property: 'ports',
            description: 'Sub-ports from the equipped item.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/item_port'),
            nullable: true,
        ),
        new OA\Property(property: 'equipped_item', ref: '#/components/schemas/game_port_item', nullable: true),
    ],
    type: 'object'
)]
/** @param array $resource Individual port entry from stdItem.Ports array */
class ItemPortResource extends AbstractBaseResource
{
    use ResolvesGameVersion;

    public function toArray(Request $request): array
    {
        $itemData = null;
        if (Arr::has($this, 'EquippedItem') && Arr::get($this, 'EquippedItem') !== null && $request->routeIs('items.show')) {
            $itemData = $this->loadItemDataForVersion(Arr::get($this, 'EquippedItem'));
            $itemData?->item->setRelation('data', collect([$itemData]));
        }

        [$type, $subType] = $this->extractPortTypeAndSubtype();

        $displayName = Arr::get($this, 'DisplayName');
        if ($displayName === '@LOC_EMPTY') {
            $displayName = Str::headline(Arr::get($this, 'PortName'));
        }

        return [
            'name' => Arr::get($this, 'PortName'),
            'display_name' => $displayName,
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
            'type' => $type,
            'sub_type' => $subType,
            'tags' => $tags = Arr::get($this, 'Tags', []),
            'port_tags' => $tags !== [] ? $tags : null,
            'required_tags' => Arr::get($this, 'RequiredTags', []),
            'flags' => Arr::get($this, 'Flags', []),
            'editable' => ! Arr::get($this, 'Uneditable'),
            'uneditable' => Arr::get($this, 'Uneditable'),
            'equipped_item_uuid' => Arr::get($this, 'EquippedItem'),
            $this->mergeWhen($itemData !== null, fn () => [
                'equipped_item' => new PortItemResource($itemData->item),
            ]),

            'ports' => $itemData !== null
                ? self::collection($this->extractItemPorts($itemData))
                : null,
        ];
    }

    /**
     * Extract normalized type and sub_type from the port's CompatibleTypes or Types.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function extractPortTypeAndSubtype(): array
    {
        $compatibleTypes = Arr::get($this, 'CompatibleTypes', []);

        if (is_array($compatibleTypes) && isset($compatibleTypes[0])) {
            $type = Arr::get($compatibleTypes[0], 'Type');
            $subTypes = Arr::get($compatibleTypes[0], 'SubTypes', []);
            $subType = $subTypes[0] ?? null;

            return [$type, $subType];
        }

        $types = Arr::get($this, 'Types', []);
        if (is_array($types) && isset($types[0])) {
            [$type, $subType] = explode('.', $types[0], 2) + [null, null];

            return [$type ?: null, $subType ?: null];
        }

        return [null, null];
    }

    /**
     * Extract sub-ports from an equipped item's stdItem data.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractItemPorts(ItemData $itemData): array
    {
        $ports = Arr::get($itemData->data, 'stdItem.Ports', []);

        return is_array($ports) && $ports !== [] ? $ports : [];
    }
}
