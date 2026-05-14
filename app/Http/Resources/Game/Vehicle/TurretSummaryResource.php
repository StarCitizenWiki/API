<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_turret',
    title: 'Turret Summary',
    description: 'Manned or remote turret entry as provided by the ship data.',
    properties: [
        new OA\Property(property: 'category', description: 'Turret category (manned, remote, pdc).', type: 'string', example: 'manned', nullable: true),
        new OA\Property(property: 'display_name', description: 'Human-readable turret name.', type: 'string', example: 'hardpoint_turret_upper', nullable: true),
        new OA\Property(property: 'hardpoint_name', description: 'Hardpoint name from ship data.', type: 'string', example: 'hardpoint_turret_upper', nullable: true),
        new OA\Property(property: 'part_name', description: 'Vehicle part this turret belongs to.', type: 'string', example: 'hardpoint_turret_upper', nullable: true),
        new OA\Property(property: 'turret_type', description: 'Full turret type string (e.g. TurretBase.MannedTurret).', type: 'string', example: 'TurretBase.MannedTurret', nullable: true),
        new OA\Property(property: 'class_name', description: 'SC class name of the turret component.', type: 'string', example: 'ORIG_890J_SCItem_Turret_Upper', nullable: true),
        new OA\Property(property: 'size', description: 'Turret hardpoint size.', type: 'integer', example: 5, nullable: true),
        new OA\Property(property: 'turret', description: 'Whether this entry is a turret.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'gimballed', description: 'Whether the turret uses gimbal mounting.', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'fixed', description: 'Whether the turret is fixed.', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'mount_count', description: 'Number of weapon mounts on the turret.', type: 'integer', example: 2, nullable: true),
        new OA\Property(property: 'weapon_sizes', description: 'Aggregated weapon sizes across all mounts.', type: 'array', items: new OA\Items(type: 'integer', example: 5), nullable: true),
        new OA\Property(property: 'payload_sizes', description: 'Aggregated payload sizes across all mounts.', type: 'array', items: new OA\Items(type: 'integer', example: 5), nullable: true),
        new OA\Property(property: 'payload_types', description: 'Aggregated payload types across all mounts.', type: 'array', items: new OA\Items(type: 'string', example: 'WeaponGun.Gun'), nullable: true),
        new OA\Property(property: 'payload_class_names', description: 'Aggregated payload class names across all mounts.', type: 'array', items: new OA\Items(type: 'string', example: 'BEHR_LaserCannon_S4'), nullable: true),
        new OA\Property(
            property: 'mounts',
            description: 'Individual mount details for the turret.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'display_name', description: 'Human-readable mount name.', type: 'string', example: 'turret_left', nullable: true),
                    new OA\Property(property: 'hardpoint_name', description: 'Hardpoint name of the mount.', type: 'string', example: 'turret_left', nullable: true),
                    new OA\Property(property: 'mount_type', description: 'Mount type string (e.g. Turret.GunTurret).', type: 'string', example: 'Turret.GunTurret', nullable: true),
                    new OA\Property(property: 'class_name', description: 'SC class name of the mount component.', type: 'string', example: 'Mount_Gimbal_S4', nullable: true),
                    new OA\Property(property: 'size', description: 'Mount hardpoint size.', type: 'integer', example: 4, nullable: true),
                    new OA\Property(property: 'weapon_sizes', description: 'Weapon sizes accepted by this mount.', type: 'array', items: new OA\Items(type: 'integer', example: 4), nullable: true),
                    new OA\Property(property: 'payload_sizes', description: 'Payload sizes for this mount.', type: 'array', items: new OA\Items(type: 'integer', example: 4), nullable: true),
                    new OA\Property(property: 'payload_types', description: 'Payload types for this mount.', type: 'array', items: new OA\Items(type: 'string', example: 'WeaponGun.Gun'), nullable: true),
                    new OA\Property(property: 'payload_class_names', description: 'Payload class names equipped on this mount.', type: 'array', items: new OA\Items(type: 'string', example: 'BEHR_LaserCannon_S4'), nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'dps_total', description: 'Aggregate DPS for the turret.', type: 'number', example: 166.7, nullable: true),
        new OA\Property(property: 'sustained_dps_total', description: 'Sustained DPS for the turret.', type: 'number', example: 74.2, nullable: true),
        new OA\Property(property: 'alpha_total', description: 'Alpha (per-shot) damage for the turret.', type: 'number', example: 10.0, nullable: true),
        new OA\Property(property: 'is_pilot_slaveable', description: 'Whether the turret can be slaved to pilot control.', type: 'boolean', example: false, nullable: true),
        new OA\Property(
            property: 'weapons',
            description: 'Per-weapon breakdown with DPS and alpha data.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'uuid', description: 'Weapon UUID.', type: 'string', example: '18b795c5-25f1-444a-86c0-b5edd7cf0118', nullable: true),
                    new OA\Property(property: 'class_name', description: 'SC class name of the weapon.', type: 'string', example: 'BEHR_LaserRepeater_PDC_S1', nullable: true),
                    new OA\Property(property: 'name', description: 'Human-readable weapon name.', type: 'string', example: 'M2C "Swarm"', nullable: true),
                    new OA\Property(property: 'link', description: 'API URL for the full item detail.', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'web_url', description: 'API Web URL for the full item detail.', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'dps', description: 'Weapon DPS.', type: 'number', example: 166.7, nullable: true),
                    new OA\Property(property: 'sustained_dps', description: 'Weapon sustained DPS.', type: 'number', example: 74.2, nullable: true),
                    new OA\Property(property: 'alpha', description: 'Weapon alpha damage.', type: 'number', example: 10.0, nullable: true),
                    new OA\Property(property: 'is_pilot_slaveable', description: 'Whether this weapon can be slaved to pilot control.', type: 'boolean', example: false, nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'version', description: 'Game version code for this data.', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class TurretSummaryResource extends AbstractBaseResource
{
    use ResolvesGameVersion;

    public function toArray(Request $request): array
    {
        $mounts = $this->mountRows();

        return [
            'category' => Arr::get($this->resource, 'Category'),
            'display_name' => Arr::get($this->resource, 'DisplayName', Arr::get($this->resource, 'Name')),
            'hardpoint_name' => Arr::get($this->resource, 'HardpointName'),
            'part_name' => Arr::get($this->resource, 'PartName'),
            'turret_type' => Arr::get($this->resource, 'TurretType', Arr::get($this->resource, 'Type')),
            'class_name' => Arr::get($this->resource, 'TurretClassName', Arr::get($this->resource, 'ClassName')),
            'size' => Arr::get($this->resource, 'Size', Arr::get($this->resource, 'MaxSize', Arr::get($this->resource, 'MinSize'))),
            'turret' => Arr::get($this->resource, 'Turret'),
            'gimballed' => Arr::get($this->resource, 'Gimballed'),
            'fixed' => Arr::get($this->resource, 'Fixed'),
            'mount_count' => Arr::get($this->resource, 'MountCount', $mounts === [] ? null : count($mounts)),
            'weapon_sizes' => Arr::get($this->resource, 'WeaponSizes', $this->aggregateRawMountValues('WeaponSizes')),
            'payload_sizes' => Arr::get($this->resource, 'PayloadSizes', $this->aggregateRawMountValues('PayloadSizes')),
            'payload_types' => Arr::get($this->resource, 'PayloadTypes', $this->aggregateRawMountValues('PayloadTypes')),
            'payload_class_names' => Arr::get($this->resource, 'PayloadClassNames', $this->aggregateRawMountValues('PayloadClassNames')),
            'mounts' => $mounts,
            'dps_total' => Arr::get($this->resource, 'DpsTotal'),
            'sustained_dps_total' => Arr::get($this->resource, 'SustainedDpsTotal'),
            'alpha_total' => Arr::get($this->resource, 'AlphaTotal'),
            'is_pilot_slaveable' => Arr::get($this->resource, 'IsPilotSlaveable'),
            'weapons' => $this->weaponRows(),
            'version' => $this->gameVersionCode(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mountRows(): array
    {
        return collect($this->rawMounts())
            ->map(static fn (array $mount): array => [
                'display_name' => Arr::get($mount, 'DisplayName', Arr::get($mount, 'Name')),
                'hardpoint_name' => Arr::get($mount, 'HardpointName'),
                'mount_type' => Arr::get($mount, 'MountType', Arr::get($mount, 'Type')),
                'class_name' => Arr::get($mount, 'MountClassName', Arr::get($mount, 'ClassName')),
                'size' => Arr::get($mount, 'Size', Arr::get($mount, 'MaxSize', Arr::get($mount, 'MinSize'))),
                'weapon_sizes' => Arr::get($mount, 'WeaponSizes'),
                'payload_sizes' => Arr::get($mount, 'PayloadSizes'),
                'payload_types' => Arr::get($mount, 'PayloadTypes'),
                'payload_class_names' => Arr::get($mount, 'PayloadClassNames'),
            ])
            ->filter(static fn (array $mount): bool => $mount !== [])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rawMounts(): array
    {
        return collect(Arr::get($this->resource, 'Mounts', []))
            ->filter(static fn (mixed $mount): bool => is_array($mount))
            ->values()
            ->all();
    }

    /**
     * @return array<int, int|string>
     */
    private function aggregateRawMountValues(string $key): array
    {
        return collect($this->rawMounts())
            ->flatMap(static fn (array $mount): array => array_values(array_filter(
                Arr::wrap(Arr::get($mount, $key, [])),
                static fn (mixed $value): bool => is_int($value) || is_string($value)
            )))
            ->uniqueStrict()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function weaponRows(): array
    {
        return collect(Arr::get($this->resource, 'Weapons', []))
            ->filter(static fn (mixed $weapon): bool => is_array($weapon))
            ->map(function (array $weapon): array {
                return [
                    'uuid' => Arr::get($weapon, 'UUID'),
                    'class_name' => Arr::get($weapon, 'ClassName'),
                    'name' => Arr::get($weapon, 'Name'),
                    'link' => Arr::get($weapon, 'UUID') !== null
                        ? route('items.show', ['identifier' => Arr::get($weapon, 'UUID')])
                        : null,
                    'web_url' => Arr::get($weapon, 'UUID') !== null
                        ? route('web.items.show', ['item' => Arr::get($weapon, 'UUID')])
                        : null,
                    'dps' => Arr::get($weapon, 'Dps'),
                    'sustained_dps' => Arr::get($weapon, 'SustainedDps'),
                    'alpha' => Arr::get($weapon, 'Alpha'),
                    'is_pilot_slaveable' => Arr::get($weapon, 'IsPilotSlaveable'),
                ];
            })
            ->filter(static fn (array $weapon): bool => $weapon !== [])
            ->values()
            ->all();
    }
}
