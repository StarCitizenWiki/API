<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use App\Http\Resources\Game\Concerns\NormalizesValues;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Item\PortItemResource;
use App\Http\Resources\Game\Vehicle\Concerns\CategorizesEquipmentType;
use App\Http\Resources\Game\Vehicle\Concerns\ProcessesHardpointData;
use App\Models\Game\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        new OA\Property(property: 'sub_type', description: 'Port sub-type identifier.', type: 'string', example: 'UNDEFINED', nullable: true),
        new OA\Property(property: 'subtype', description: 'Deprecated: Use sub_type.', type: 'string', example: 'UNDEFINED', nullable: true, deprecated: true),
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
        new OA\Property(property: 'required_tags', description: 'Tags that items must have to be equipped in this port. Pass individual values as filter[tags] to the items API.', type: 'array', items: new OA\Items(type: 'string'), example: ['Ship_Dock_Refuel'], nullable: true),
        new OA\Property(property: 'port_tags', description: 'Identity tags this port provides. Used to filter items by RequiredTags compatibility - an item can attach if its RequiredTags is empty or fully contained in these tags. Pass as filter[port_tags] to the items API.', type: 'array', items: new OA\Items(type: 'string'), example: ['AEGS_Avenger_Base'], nullable: true),
        new OA\Property(property: 'version', description: 'Game version code for this data.', type: 'string', nullable: true),
        new OA\Property(
            property: 'attached_vehicle',
            description: 'Resolved vehicle attached to a NOITEM_Vehicle port (e.g. a snub ship or command module).',
            properties: [
                new OA\Property(property: 'uuid', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'class_name', type: 'string'),
                new OA\Property(property: 'size_class', type: 'integer', nullable: true),
                new OA\Property(property: 'is_spaceship', type: 'boolean', nullable: true),
                new OA\Property(property: 'is_vehicle', type: 'boolean', nullable: true),
                new OA\Property(property: 'is_gravlev', type: 'boolean', nullable: true),
                new OA\Property(property: 'link', description: 'API URL for the vehicle.', type: 'string', nullable: true),
                new OA\Property(property: 'web_url', description: 'Web URL for the vehicle page.', type: 'string', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class PortResource extends AbstractBaseResource
{
    use CategorizesEquipmentType;
    use ExtractsJsonData;
    use NormalizesValues;
    use ProcessesHardpointData;
    use ResolvesGameVersion;

    /** @var array<string, mixed>|null|false Cached result of findAttachedVehicleChild, false = not yet resolved */
    private array|null|false $attachedVehicleChildCache = false;

    public function __construct($resource, int|string|null $key = null, private readonly bool $isChild = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $resolvedItem = null;
        $attachedVehicle = null;
        if (
            $request->routeIs('vehicles.show') ||
            $request->routeIs('v2.vehicles.show') ||
            $request->routeIs('v3.vehicles.show')
        ) {
            $resolvedItem = $this->loadEquippedItem();
            $attachedVehicle = $this->resolveAttachedVehicle($request);
        }

        $health = $this->extractHealth($resolvedItem);
        [$type, $subtype] = $this->extractTypeAndSubtype();
        $compatibleTypes = $this->buildCompatibleTypes();

        $minSize = Arr::get($this, 'MinSize');
        $maxSize = Arr::get($this, 'MaxSize');

        return [
            'name' => Arr::get($this, 'HardpointName'),
            'position' => Arr::get($this, 'Position'),
            'class_name' => Arr::get($this, 'ClassName'),
            'editable' => Arr::get($this, 'Editable'),
            'editable_children' => Arr::get($this, 'EditableChildren'),
            'equipped_item_uuid' => Arr::get($this->resource, 'UUID'),
            'type' => $type,
            'sub_type' => $subtype,
            'subtype' => $subtype,
            'sizes' => ($minSize !== null || $maxSize !== null) ? [
                'min' => $minSize,
                'max' => $maxSize,
            ] : null,
            'compatible_types' => $compatibleTypes !== [] ? $compatibleTypes : null,
            'health' => $health,
            'equipped_item' => $resolvedItem !== null ? new PortItemResource($resolvedItem) : null,
            'attached_vehicle' => $attachedVehicle,
            'ports' => $this->shouldIncludeChildren() ? self::collection(collect($this->getChildrenArray())->map(fn ($port) => new self($port, isChild: true))) : null,
            'category_label' => ! $this->isChild ? $this->categorizePort() : null,
            'required_tags' => self::normalizeTagList(Arr::get($this, 'RequiredTags')),
            'port_tags' => $this->buildPortTags(),
            'version' => $this->gameVersionCode(),
        ];
    }

    private function categorizePort(): string
    {
        [$type, $subtype] = $this->extractTypeAndSubtype();

        $category = $this->categorizeEquipmentType(
            $type,
            $subtype,
            Arr::get($this, 'ClassName'),
        );

        // When type is empty/unknown, fall back to hardpoint-name patterns
        if ($category === 'Other' && ($type === '' || $type === null)) {
            $hardpoint = Arr::get($this, 'HardpointName');
            if ($hardpoint !== null) {
                $fallback = $this->categorizeByHardpointName($hardpoint);
                if ($fallback !== 'Other') {
                    return $fallback;
                }
            }
        }

        // Promote Docking parents with an attached vehicle child to "Docked Vehicles"
        if ($category === 'Docking' && $this->findAttachedVehicleChild() !== null) {
            return 'Docked Vehicles';
        }

        return $category;
    }

    /**
     * Check whether a port array represents a NOITEM_Vehicle type.
     */
    private function isNoItemVehicle(array $port): bool
    {
        $type = Arr::get($port, 'Type', '');
        $compatibleTypes = Arr::get($port, 'CompatibleTypes') ?? Arr::get($port, 'ItemTypes', []);

        return str_starts_with($type, 'NOITEM_Vehicle')
            || collect($compatibleTypes)->contains(fn (array $it): bool => ($it['Type'] ?? '') === 'NOITEM_Vehicle');
    }

    /**
     * Resolve an attached vehicle for this port or its first NOITEM_Vehicle child.
     *
     * Returns a lightweight link array when a vehicle UUID resolves to a game vehicle.
     */
    private function resolveAttachedVehicle(Request $request): ?array
    {
        $uuid = $this->resolveAttachedVehicleUuid();

        if ($uuid === null) {
            return null;
        }

        $vehicle = $this->loadVehicleForVersion($uuid);

        if ($vehicle === null) {
            return null;
        }

        return $this->formatAttachedVehicle($vehicle, $request);
    }

    /**
     * Find the vehicle UUID for this port — either directly or from a NOITEM_Vehicle child.
     */
    private function resolveAttachedVehicleUuid(): ?string
    {
        if ($this->isNoItemVehicle($this->resource)) {
            $uuid = Arr::get($this->resource, 'UUID');

            if (is_string($uuid) && $uuid !== '') {
                return $uuid;
            }
        }

        $child = $this->findAttachedVehicleChild();
        $uuid = Arr::get($child, 'UUID');

        if (is_string($uuid) && $uuid !== '') {
            return $uuid;
        }

        return null;
    }

    /**
     * Find the first child port that represents an attached vehicle.
     *
     * @return array<string, mixed>|null
     */
    private function findAttachedVehicleChild(): ?array
    {
        if ($this->attachedVehicleChildCache !== false) {
            return $this->attachedVehicleChildCache;
        }

        $children = Arr::get($this->resource, 'Loadout', []);

        return $this->attachedVehicleChildCache = array_find($children, fn ($child) => $this->isNoItemVehicle($child) && is_string($uuid = Arr::get($child, 'UUID')) && $uuid !== '');
    }

    /**
     * Format an attached vehicle into a lightweight link array.
     *
     * @return array<string, mixed>
     */
    private function formatAttachedVehicle(Vehicle $vehicle, Request $request): array
    {
        $vehicleData = $vehicle->data->first();

        return [
            'uuid' => $vehicle->uuid,
            'name' => $vehicleData?->display_name ?? $vehicleData?->name,
            'class_name' => $vehicleData?->class_name,
            'size_class' => $vehicleData?->size,
            'is_spaceship' => $vehicleData?->is_spaceship,
            'is_vehicle' => $vehicleData?->is_vehicle,
            'is_gravlev' => $vehicleData?->is_gravlev,
            'link' => $this->urlWithVersion(route('vehicles.show', ['vehicle' => $vehicle->uuid]), $request),
            'web_url' => $this->urlWithVersion(route('web.vehicles.show', ['vehicle' => $vehicle->slug]), $request),
        ];
    }
}
