<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle_turret',
    title: 'Turret Summary',
    description: 'Manned or remote turret entry as provided by the ship data.',
    properties: [
        new OA\Property(property: 'category', type: 'string', example: 'manned', nullable: true),
        new OA\Property(property: 'display_name', type: 'string', example: 'hardpoint_turret_upper', nullable: true),
        new OA\Property(property: 'hardpoint_name', type: 'string', example: 'hardpoint_turret_upper', nullable: true),
        new OA\Property(property: 'part_name', type: 'string', example: 'hardpoint_turret_upper', nullable: true),
        new OA\Property(property: 'turret_type', type: 'string', example: 'TurretBase.MannedTurret', nullable: true),
        new OA\Property(property: 'class_name', type: 'string', example: 'ORIG_890J_SCItem_Turret_Upper', nullable: true),
        new OA\Property(property: 'size', type: 'integer', example: 5, nullable: true),
        new OA\Property(property: 'turret', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'gimballed', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'fixed', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'mount_count', type: 'integer', example: 2, nullable: true),
        new OA\Property(property: 'weapon_sizes', type: 'array', items: new OA\Items(type: 'integer', example: 5), nullable: true),
        new OA\Property(property: 'payload_sizes', type: 'array', items: new OA\Items(type: 'integer', example: 5), nullable: true),
        new OA\Property(property: 'payload_types', type: 'array', items: new OA\Items(type: 'string', example: 'WeaponGun.Gun'), nullable: true),
        new OA\Property(property: 'payload_class_names', type: 'array', items: new OA\Items(type: 'string', example: 'BEHR_LaserCannon_S4'), nullable: true),
        new OA\Property(
            property: 'mounts',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'display_name', type: 'string', example: 'turret_left', nullable: true),
                    new OA\Property(property: 'hardpoint_name', type: 'string', example: 'turret_left', nullable: true),
                    new OA\Property(property: 'mount_type', type: 'string', example: 'Turret.GunTurret', nullable: true),
                    new OA\Property(property: 'class_name', type: 'string', example: 'Mount_Gimbal_S4', nullable: true),
                    new OA\Property(property: 'size', type: 'integer', example: 4, nullable: true),
                    new OA\Property(property: 'weapon_sizes', type: 'array', items: new OA\Items(type: 'integer', example: 4), nullable: true),
                    new OA\Property(property: 'payload_sizes', type: 'array', items: new OA\Items(type: 'integer', example: 4), nullable: true),
                    new OA\Property(property: 'payload_types', type: 'array', items: new OA\Items(type: 'string', example: 'WeaponGun.Gun'), nullable: true),
                    new OA\Property(property: 'payload_class_names', type: 'array', items: new OA\Items(type: 'string', example: 'BEHR_LaserCannon_S4'), nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
    ],
    type: 'object'
)]
class TurretSummaryResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [];
    }

    public function toArray(Request $request): array
    {
        $mounts = $this->mountRows();

        return array_filter([
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
        ], static fn ($value) => $value !== null && $value !== []);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mountRows(): array
    {
        return collect($this->rawMounts())
            ->map(static fn (array $mount): array => array_filter([
                'display_name' => Arr::get($mount, 'DisplayName', Arr::get($mount, 'Name')),
                'hardpoint_name' => Arr::get($mount, 'HardpointName'),
                'mount_type' => Arr::get($mount, 'MountType', Arr::get($mount, 'Type')),
                'class_name' => Arr::get($mount, 'MountClassName', Arr::get($mount, 'ClassName')),
                'size' => Arr::get($mount, 'Size', Arr::get($mount, 'MaxSize', Arr::get($mount, 'MinSize'))),
                'weapon_sizes' => Arr::get($mount, 'WeaponSizes'),
                'payload_sizes' => Arr::get($mount, 'PayloadSizes'),
                'payload_types' => Arr::get($mount, 'PayloadTypes'),
                'payload_class_names' => Arr::get($mount, 'PayloadClassNames'),
            ], static fn ($value) => $value !== null && $value !== []))
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
}
