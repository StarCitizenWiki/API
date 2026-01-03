<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Vehicle\Concerns\ProcessesHardpointData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_port',
    title: 'Vehicle Port / Hardpoint',
    description: 'Ports (hardpoints) as defined in scunpacked ship files. Nested ports are supported.',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'hardpoint_weapon_class2_nose'),
        new OA\Property(property: 'position', type: 'string', example: 'left', nullable: true),
        new OA\Property(property: 'sizes', properties: [
            new OA\Property(property: 'min', type: 'integer', example: 1, nullable: true),
            new OA\Property(property: 'max', type: 'integer', example: 1, nullable: true),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'class_name', type: 'string', example: 'LFSP_TYDT_S01_ComfortAir', nullable: true),
        new OA\Property(property: 'health', type: 'number', example: 300, nullable: true),
        new OA\Property(property: 'editable', type: 'boolean', nullable: true),
        new OA\Property(property: 'editable_children', type: 'boolean', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', example: 'LifeSupportGenerator', nullable: true),
        new OA\Property(property: 'subtype', type: 'string', example: 'UNDEFINED', nullable: true),
        new OA\Property(
            property: 'compatible_types',
            description: 'Port compatibility straight from the ship data.',
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'type', type: 'string', example: 'LifeSupportGenerator', nullable: true),
                new OA\Property(property: 'subtype', type: 'string', example: 'UNDEFINED', nullable: true),
            ], type: 'object'),
            nullable: true
        ),
        new OA\Property(property: 'equipped_item', ref: '#/components/schemas/game_vehicle_port_item', nullable: true),
        new OA\Property(
            property: 'ports',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_port'),
            nullable: true
        ),
    ],
    type: 'object'
)]
class PortResource extends AbstractBaseResource
{
    use ExtractsJsonData;
    use ProcessesHardpointData;
    use ResolvesGameVersion;

    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        $resolvedItem = null;
        if ($request->routeIs('vehicles.show')) {
            $resolvedItem = $this->loadEquippedItem();
        }

        $health = $this->extractHealth($resolvedItem);
        [$type, $subtype] = $this->extractTypeAndSubtype();
        $compatibleTypes = $this->buildCompatibleTypes();

        $minSize = Arr::get($this, 'MinSize');
        $maxSize = Arr::get($this, 'MaxSize');

        $data = [
            'name' => Arr::get($this, 'HardpointName'),
            'position' => Arr::get($this, 'Position'),
            'class_name' => Arr::get($this, 'ClassName'),
            'editable' => Arr::get($this, 'Editable'),
            'editable_children' => Arr::get($this, 'EditableChildren'),
            'equipped_item_uuid' => Arr::get($this->resource, 'UUID'),
            'type' => $type,
            'subtype' => $subtype,
        ];

        if ($minSize !== null || $maxSize !== null) {
            $data['sizes'] = [
                'min' => $minSize,
                'max' => $maxSize,
            ];
        }

        if ($compatibleTypes !== []) {
            $data['compatible_types'] = $compatibleTypes;
        }

        if ($health !== null) {
            $data['health'] = $health;
        }

        if ($resolvedItem !== null) {
            $data['equipped_item'] = new PortItemResource($resolvedItem);
        }

        if ($this->shouldIncludeChildren()) {
            $data['ports'] = self::collection($this->getChildrenArray());
        }

        return array_filter(
            $data,
            static fn ($value) => $value !== null && $value !== [],
            ARRAY_FILTER_USE_BOTH
        );
    }
}
