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
        new OA\Property(property: 'name', description: 'Hardpoint name from scunpacked data.', type: 'string', example: 'hardpoint_weapon_class2_nose'),
        new OA\Property(property: 'position', description: 'Positional label (e.g. left, right, nose).', type: 'string', example: 'left', nullable: true),
        new OA\Property(property: 'min_size', description: 'Minimum item size the port accepts.', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'max_size', description: 'Maximum item size the port accepts.', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'class_name', description: 'SC class name of the port component.', type: 'string', example: 'LFSP_TYDT_S01_ComfortAir', nullable: true),
        new OA\Property(property: 'health', description: 'Port health points.', type: 'number', example: 300, nullable: true),
        new OA\Property(property: 'type', description: 'Port type (e.g. WeaponGun, Shield, PowerPlant).', type: 'string', example: 'LifeSupportGenerator', nullable: true),
        new OA\Property(property: 'sub_type', description: 'Port sub-type identifier.', type: 'string', example: 'UNDEFINED', nullable: true),
        new OA\Property(property: 'pilot_slaveable', description: 'Whether the port can be slaved to a pilot.', type: 'boolean', nullable: true),
        new OA\Property(property: 'item', ref: '#/components/schemas/game_vehicle_hardpoint_item', description: 'Equipped item details, resolved from the game database.', nullable: true),
        new OA\Property(
            property: 'children',
            description: 'Nested child hardpoints.',
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
        if (
            $request->routeIs('vehicles.show') ||
            $request->routeIs('v2.vehicles.show') ||
            $request->routeIs('v3.vehicles.show')
        ) {
            $resolvedItem = $this->loadEquippedItem();
        }

        $health = $this->extractHealth($resolvedItem);
        [$type, $subtype] = $this->extractTypeAndSubtype();

        return [
            'name' => Arr::get($this, 'HardpointName'),
            'position' => Arr::get($this, 'Position'),
            'class_name' => Arr::get($this, 'ClassName'),
            'min_size' => Arr::get($this, 'MinSize'),
            'max_size' => Arr::get($this, 'MaxSize'),
            'health' => $health,
            'type' => $type,
            'sub_type' => $subtype,
            'pilot_slaveable' => Arr::get($this, 'IsPilotSlaveable'),
            'item' => $resolvedItem !== null ? new HardpointItemResource($resolvedItem) : null,
            'children' => $this->shouldIncludeChildren() ? self::collection($this->getChildrenArray()) : null,
        ];
    }
}
