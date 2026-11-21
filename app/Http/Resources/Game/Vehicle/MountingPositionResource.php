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
            new OA\Property(property: 'combat_stats', description: 'Reported stats for the item installed at this port. Unreported metrics are omitted.', properties: [
                new OA\Property(property: 'dps', description: 'Gun damage per second.', type: 'number'),
                new OA\Property(property: 'sustained_dps', description: 'Sustained gun damage per second.', type: 'number'),
                new OA\Property(property: 'alpha', description: 'Gun damage per shot.', type: 'number'),
                new OA\Property(property: 'missile_damage', description: 'Total damage of this installed missile, not damage per second.', type: 'number', example: 1600),
            ], type: 'object'),
        ], type: 'object')),
        new OA\Property(property: 'operating_stations', description: 'Verified stations a player can occupy to fire the installed weapon or use the installed tool. Omitted for vacant positions. On occupied positions, an absent or empty list means unknown. A listed station does not rule out other stations. Older technical control routes are never used as a fallback.', type: 'array', items: new OA\Items(required: ['kind', 'seat_port_id', 'name'], properties: [
            new OA\Property(property: 'kind', type: 'string', enum: ['Pilot', 'Crew', 'InTurret']),
            new OA\Property(property: 'seat_port_id', description: 'Snapshot-local loadout port ID of the installed seat, or of the turret with an integrated seat.', type: 'string'),
            new OA\Property(property: 'name', description: 'Station name supplied by the dumper.', type: 'string', example: 'Pilot seat'),
        ], type: 'object')),
        new OA\Property(property: 'combat_stats', description: 'Combat stats for this position. The installed totals sum reported values across payload ports and omit unreported metrics.', properties: [
            new OA\Property(property: 'dps', description: 'Total gun damage per second for installed payload.', type: 'number'),
            new OA\Property(property: 'sustained_dps', description: 'Total sustained gun damage per second for installed payload.', type: 'number'),
            new OA\Property(property: 'alpha', description: 'Sum of gun damage per shot for installed payload.', type: 'number'),
            new OA\Property(property: 'missile_damage', description: 'Sum of damage for missiles actually loaded in this position. Not DPS, salvo damage, or full rack capacity damage.', type: 'number', example: 1600),
        ], type: 'object'),
    ],
    type: 'object',
)]
class MountingPositionResource extends AbstractBaseResource
{
    private const array COMBAT_METRICS = ['Dps', 'SustainedDps', 'Alpha', 'MissileDamage'];

    public function toArray(Request $request): array
    {
        $position = $this->resource;
        $mapped = self::mapFields($position, [
            'PortId', 'HardpointName', 'Occupied', 'MinSize', 'MaxSize', 'InstalledSize', 'MountType', 'TurretRole', 'IsPilotSlaveable',
        ]);

        $mapped['payload'] = array_map(static function (array $payload): array {
            $item = self::mapFields($payload, ['PortId', 'HardpointName', 'UUID', 'ClassName', 'Name', 'Type', 'Size']);
            $combatStats = self::mapFields($payload['CombatStats'] ?? [], self::COMBAT_METRICS);
            if ($combatStats !== []) {
                $item['combat_stats'] = $combatStats;
            }

            return $item;
        }, $position['Payload']);

        if (($position['Occupied'] ?? false) && isset($position['OperatingStations'])) {
            $mapped['operating_stations'] = array_map(static fn (array $station): array => self::mapFields($station, [
                'Kind', 'SeatPortId', 'Name',
            ]), $position['OperatingStations']);
        }

        $combatStats = self::mapFields($position['CombatStats'] ?? [], self::COMBAT_METRICS);
        if ($combatStats !== []) {
            $mapped['combat_stats'] = $combatStats;
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
