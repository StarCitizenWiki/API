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
    schema: 'game_vehicle_hardpoint',
    title: 'Vehicle Hardpoint v2',
    description: 'Deprecated: For v2 API compatibility. Ports (hardpoints) as defined in scunpacked ship files.',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'hardpoint_weapon_class2_nose'),
        new OA\Property(property: 'position', type: 'string', example: 'left', nullable: true),
        new OA\Property(property: 'min_size', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'max_size', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'class_name', type: 'string', example: 'LFSP_TYDT_S01_ComfortAir', nullable: true),
        new OA\Property(property: 'health', type: 'number', example: 300, nullable: true),
        new OA\Property(property: 'type', type: 'string', example: 'LifeSupportGenerator', nullable: true),
        new OA\Property(property: 'sub_type', type: 'string', example: 'UNDEFINED', nullable: true),
        new OA\Property(property: 'item', ref: '#/components/schemas/game_vehicle_hardpoint_item', nullable: true),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_hardpoint'),
            nullable: true
        ),
    ],
    type: 'object',
    deprecated: true
)]
class HardpointResource extends AbstractBaseResource
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

        $data = [
            'name' => Arr::get($this, 'HardpointName'),
            'position' => Arr::get($this, 'Position'),
            'class_name' => Arr::get($this, 'ClassName'),
            'min_size' => Arr::get($this, 'MinSize'),
            'max_size' => Arr::get($this, 'MaxSize'),
            'health' => $health,
            'type' => $type,
            'sub_type' => $subtype,
        ];

        if ($resolvedItem !== null) {
            $data['item'] = new HardpointItemResource($resolvedItem);
        }

        if ($this->shouldIncludeChildren()) {
            $data['children'] = self::collection($this->getChildrenArray());
        }

        return array_filter(
            $data,
            static fn ($value) => $value !== null && $value !== [],
            ARRAY_FILTER_USE_BOTH
        );
    }
}
