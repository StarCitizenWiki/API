<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Item\PortItemResource;
use App\Http\Resources\Game\Vehicle\Concerns\ProcessesHardpointData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_port',
    title: 'Vehicle Port / Hardpoint',
    description: 'Ports (hardpoints) as defined in scunpacked ship files. Nested ports are supported.',
    properties: [
        new OA\Property(property: 'name', description: 'Hardpoint name from scunpacked ship data.', type: 'string', example: 'hardpoint_weapon_class2_nose'),
        new OA\Property(property: 'position', description: 'Positional label (e.g. left, right, nose).', type: 'string', example: 'left', nullable: true),
        new OA\Property(property: 'sizes', description: 'Minimum and maximum item sizes the port accepts.', properties: [
            new OA\Property(property: 'min', description: 'Minimum item size.', type: 'integer', example: 1, nullable: true),
            new OA\Property(property: 'max', description: 'Maximum item size.', type: 'integer', example: 1, nullable: true),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'class_name', description: 'SC class name of the port component.', type: 'string', example: 'LFSP_TYDT_S01_ComfortAir', nullable: true),
        new OA\Property(property: 'health', description: 'Port health points.', type: 'number', example: 300, nullable: true),
        new OA\Property(property: 'editable', description: 'Whether the port can be modified in-game.', type: 'boolean', nullable: true),
        new OA\Property(property: 'editable_children', description: 'Whether child ports can be modified in-game.', type: 'boolean', nullable: true),
        new OA\Property(property: 'equipped_item_uuid', description: 'UUID of the item currently equipped in this port.', type: 'string', nullable: true),
        new OA\Property(property: 'type', description: 'Port type (e.g. WeaponGun, Shield, PowerPlant).', type: 'string', example: 'LifeSupportGenerator', nullable: true),
        new OA\Property(property: 'subtype', description: 'Port sub-type identifier.', type: 'string', example: 'UNDEFINED', nullable: true),
        new OA\Property(property: 'category_label', description: 'Human-readable category label. Only present on parent ports (e.g. Weapons, Shields, Thrusters).', type: 'string', example: 'Weapons', nullable: true),
        new OA\Property(
            property: 'compatible_types',
            description: 'Port compatibility list from ship data.',
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(property: 'type', description: 'Compatible item type.', type: 'string', example: 'LifeSupportGenerator', nullable: true),
                new OA\Property(property: 'sub_types', description: 'List of compatible sub-types for this port type.', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
            ], type: 'object'),
            nullable: true
        ),
        new OA\Property(property: 'equipped_item', ref: '#/components/schemas/game_port_item', description: 'Full details of the equipped item, resolved from the game database.', nullable: true),
        new OA\Property(
            property: 'ports',
            description: 'Nested child ports (hardpoints).',
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
        if (
            $request->routeIs('vehicles.show') ||
            $request->routeIs('v2.vehicles.show') ||
            $request->routeIs('v3.vehicles.show')
        ) {
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
            $data['ports'] = ChildPortResource::collection($this->getChildrenArray());
        }

        if (! ($this instanceof ChildPortResource)) {
            $category = $this->categorize();
            $data['category_label'] = $category;
        }

        return array_filter(
            $data,
            static fn ($value) => $value !== null && $value !== [],
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function categorize(): string
    {
        [$type, $subtype] = $this->extractTypeAndSubtype();

        $category = match ($type) {
            'CargoGrid' => 'Cargo Grids',
            'Cooler' => 'Coolers',
            'EMP' => 'EMP',
            'FlightController' => 'Flight Controller',
            'FuelTank', 'QuantumFuelTank', 'FuelIntake' => 'Fuel',
            'LifeSupportGenerator' => 'Life Support',
            'MainThruster', 'ManneuverThruster' => 'Thrusters', // TODO
            'MissileLauncher', 'BombRack' => 'Missile & Bomb Racks',
            'Paint' => 'Paints',
            'PowerPlant' => 'Power Plants',
            'QuantumDrive' => 'Quantum Drives',
            'QuantumInterdictionGenerator' => 'QED',
            'Radar' => 'Radars',
            'Shield' => 'Shields',
            'Turret', 'TurretBase' => 'Turrets',
            'WeaponDefensive' => 'Counter Measures',
            'WeaponGun' => 'Weapons',
            default => 'Other',
        };

        if ($category === 'Turrets') {
            if (Str::contains(Arr::get($this, 'ClassName'), 'Remote')) {
                $category = 'Remote Turrets';
            } elseif ($subtype === 'MannedTurret') {
                $category = 'Manned Turrets';
            } elseif ($subtype === 'PDCTurret') {
                $category = 'PDC Turrets';
            }
        }

        if ($category === 'Other' && str_starts_with($type, 'Flair')) {
            $category = 'Customization';
        }

        return $category;
    }
}
