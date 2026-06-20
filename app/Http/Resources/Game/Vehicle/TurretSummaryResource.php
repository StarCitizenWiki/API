<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use Illuminate\Http\Request;
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

    /**
     * Memo for rawMounts(): declared as unset rather than null so null stays
     * a valid (if unlikely) filtered result. See rawMounts().
     */
    private array $rawMountsMemo;

    public function toArray(Request $request): array
    {
        $res = $this->resource;
        $mounts = $this->mountRows();

        return [
            'category' => $res['Category'] ?? null,
            'display_name' => $res['DisplayName'] ?? ($res['Name'] ?? null),
            'hardpoint_name' => $res['HardpointName'] ?? null,
            'part_name' => $res['PartName'] ?? null,
            'turret_type' => $res['TurretType'] ?? ($res['Type'] ?? null),
            'class_name' => $res['TurretClassName'] ?? ($res['ClassName'] ?? null),
            'size' => $res['Size'] ?? ($res['MaxSize'] ?? ($res['MinSize'] ?? null)),
            'turret' => $res['Turret'] ?? null,
            'gimballed' => $res['Gimballed'] ?? null,
            'fixed' => $res['Fixed'] ?? null,
            'mount_count' => $res['MountCount'] ?? ($mounts === [] ? null : count($mounts)),
            'weapon_sizes' => $res['WeaponSizes'] ?? $this->aggregateRawMountValues('WeaponSizes'),
            'payload_sizes' => $res['PayloadSizes'] ?? $this->aggregateRawMountValues('PayloadSizes'),
            'payload_types' => $res['PayloadTypes'] ?? $this->aggregateRawMountValues('PayloadTypes'),
            'payload_class_names' => $res['PayloadClassNames'] ?? $this->aggregateRawMountValues('PayloadClassNames'),
            'mounts' => $mounts,
            'dps_total' => $res['DpsTotal'] ?? null,
            'sustained_dps_total' => $res['SustainedDpsTotal'] ?? null,
            'alpha_total' => $res['AlphaTotal'] ?? null,
            'is_pilot_slaveable' => $res['IsPilotSlaveable'] ?? null,
            'weapons' => $this->weaponRows($request),
            'version' => $this->gameVersionCode(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mountRows(): array
    {
        $rows = array_map(static fn (array $mount): array => [
            'display_name' => $mount['DisplayName'] ?? ($mount['Name'] ?? null),
            'hardpoint_name' => $mount['HardpointName'] ?? null,
            'mount_type' => $mount['MountType'] ?? ($mount['Type'] ?? null),
            'class_name' => $mount['MountClassName'] ?? ($mount['ClassName'] ?? null),
            'size' => $mount['Size'] ?? ($mount['MaxSize'] ?? ($mount['MinSize'] ?? null)),
            'weapon_sizes' => $mount['WeaponSizes'] ?? null,
            'payload_sizes' => $mount['PayloadSizes'] ?? null,
            'payload_types' => $mount['PayloadTypes'] ?? null,
            'payload_class_names' => $mount['PayloadClassNames'] ?? null,
        ], $this->rawMounts());

        return array_values(array_filter($rows, static fn (array $mount): bool => $mount !== []));
    }

    /**
     * Filtered mounts, memoized: toArray() pulls this via mountRows() once
     * and via aggregateRawMountValues() up to four times (per aggregation
     * key), so computing it once avoids four repeated Collection builds.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rawMounts(): array
    {
        if (! isset($this->rawMountsMemo)) {
            $this->rawMountsMemo = array_values(array_filter(
                $this->resource['Mounts'] ?? [],
                static fn (mixed $mount): bool => is_array($mount),
            ));
        }

        return $this->rawMountsMemo;
    }

    /**
     * @return array<int, int|string>
     */
    private function aggregateRawMountValues(string $key): array
    {
        $seen = [];
        $result = [];
        foreach ($this->rawMounts() as $mount) {
            foreach (array_filter(
                (array) ($mount[$key] ?? []),
                static fn (mixed $value): bool => is_int($value) || is_string($value)
            ) as $value) {
                if (! in_array($value, $seen, true)) {
                    $seen[] = $value;
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function weaponRows(Request $request): array
    {
        $rows = array_map(function (array $weapon) use ($request): array {
            $uuid = $weapon['UUID'] ?? null;

            return [
                'uuid' => $uuid,
                'class_name' => $weapon['ClassName'] ?? null,
                'name' => $weapon['Name'] ?? null,
                'link' => $uuid !== null
                    ? route('items.show', ['identifier' => $uuid])
                    : null,
                'web_url' => $uuid !== null
                    ? $this->urlWithVersion(route('web.items.show', ['item' => $uuid]), $request)
                    : null,
                'dps' => $weapon['Dps'] ?? null,
                'sustained_dps' => $weapon['SustainedDps'] ?? null,
                'alpha' => $weapon['Alpha'] ?? null,
                'is_pilot_slaveable' => $weapon['IsPilotSlaveable'] ?? null,
            ];
        }, array_filter(
            $this->resource['Weapons'] ?? [],
            static fn (mixed $weapon): bool => is_array($weapon),
        ));

        return array_values(array_filter($rows, static fn (array $weapon): bool => $weapon !== []));
    }
}
