<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_mounting_position',
    required: ['port_id', 'occupied', 'min_size', 'max_size', 'payload'],
    properties: [
        new OA\Property(property: 'port_id', description: 'ID of the corresponding loadout port in this snapshot.', type: 'string'),
        new OA\Property(property: 'hardpoint_name', type: 'string'),
        new OA\Property(property: 'occupied', type: 'boolean'),
        new OA\Property(property: 'min_size', description: 'Minimum size accepted by the port.', type: 'integer'),
        new OA\Property(property: 'max_size', description: 'Maximum size accepted by the port.', type: 'integer'),
        new OA\Property(property: 'installed_size', description: 'Size of the installed item.', type: 'integer'),
        new OA\Property(property: 'mount_type', description: 'Installed mount type. Absent for vacant positions or unknown types.', type: 'string', enum: ['Fixed', 'Gimbal', 'Turret', 'Rack', 'Missile', 'Tool', 'Device']),
        new OA\Property(property: 'turret_role', type: 'string', enum: ['Manned', 'Remote', 'Pdc', 'Autonomous']),
        new OA\Property(property: 'is_pilot_slaveable', description: 'Legacy slaveability flag, not a control route.', type: 'boolean'),
        new OA\Property(property: 'payload', type: 'array', items: new OA\Items(required: ['port_id'], properties: [
            new OA\Property(property: 'port_id', type: 'string'),
            new OA\Property(property: 'hardpoint_name', type: 'string'),
            new OA\Property(property: 'uuid', type: 'string'),
            new OA\Property(property: 'class_name', type: 'string'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'type', type: 'string'),
            new OA\Property(property: 'size', type: 'integer'),
        ], type: 'object')),
        new OA\Property(property: 'control_routes', description: 'Known control routes. Absence means unknown.', type: 'array', items: new OA\Items(required: ['kind', 'operator', 'seat_port_id'], properties: [
            new OA\Property(property: 'kind', type: 'string', enum: ['Controller', 'InTurret']),
            new OA\Property(property: 'operator', type: 'string', enum: ['Pilot', 'Crew']),
            new OA\Property(property: 'seat_port_id', type: 'string'),
            new OA\Property(property: 'controller_port_id', type: 'string'),
            new OA\Property(property: 'control', description: 'Game-data binding for an installed module controller: Aim means a weapon controller targets the turret; Payload means a salvage controller targets the tractor beam. Neither value describes or limits what the player can do in-game. Omitted when no such binding is reported.', type: 'string', enum: ['Aim', 'Payload']),
        ], type: 'object')),
        new OA\Property(property: 'combat_stats', description: 'Combat stats reported by the dump without recalculation.', properties: [
            new OA\Property(property: 'dps_total', type: 'number'),
            new OA\Property(property: 'sustained_dps_total', type: 'number'),
            new OA\Property(property: 'alpha_total', type: 'number'),
            new OA\Property(property: 'is_pilot_slaveable', type: 'boolean'),
            new OA\Property(property: 'weapons', type: 'array', items: new OA\Items(properties: [
                new OA\Property(property: 'uuid', type: 'string'),
                new OA\Property(property: 'class_name', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'dps', type: 'number'),
                new OA\Property(property: 'sustained_dps', type: 'number'),
                new OA\Property(property: 'alpha', type: 'number'),
                new OA\Property(property: 'is_pilot_slaveable', type: 'boolean'),
            ], type: 'object')),
        ], type: 'object'),
    ],
    type: 'object',
)]
class MountingPositionResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $position = $this->resource;
        $mapped = self::mapFields($position, [
            'PortId', 'HardpointName', 'Occupied', 'MinSize', 'MaxSize', 'InstalledSize', 'MountType', 'TurretRole', 'IsPilotSlaveable',
        ]);

        $mapped['payload'] = array_map(static fn (array $payload): array => self::mapFields($payload, [
            'PortId', 'HardpointName', 'UUID', 'ClassName', 'Name', 'Type', 'Size',
        ]), $position['Payload']);

        if (isset($position['ControlRoutes'])) {
            $mapped['control_routes'] = array_map(static fn (array $route): array => self::mapFields($route, [
                'Kind', 'Operator', 'SeatPortId', 'ControllerPortId', 'Control',
            ]), $position['ControlRoutes']);
        }

        if (isset($position['CombatStats'])) {
            $stats = $position['CombatStats'];
            $mapped['combat_stats'] = self::mapFields($stats, ['DpsTotal', 'SustainedDpsTotal', 'AlphaTotal', 'IsPilotSlaveable']);

            if (isset($stats['Weapons'])) {
                $mapped['combat_stats']['weapons'] = array_map(static fn (array $weapon): array => self::mapFields($weapon, [
                    'UUID', 'ClassName', 'Name', 'Dps', 'SustainedDps', 'Alpha', 'IsPilotSlaveable',
                ]), $stats['Weapons']);
            }
        }

        // The dump's legacy summary facts (LegacyBucket / LegacyOrdinal /
        // LegacySize) stay internal: mounting_positions exposes only the
        // current classification and size fields; turrets.* serves the exact
        // legacy values unchanged.
        return $mapped;
    }

    private static function mapFields(array $data, array $fields): array
    {
        $mapped = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $mapped[$field === 'UUID' ? 'uuid' : Str::snake($field)] = $data[$field];
            }
        }

        return $mapped;
    }
}
