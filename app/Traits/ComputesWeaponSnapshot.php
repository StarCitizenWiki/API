<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\Game\WeaponSnapshotService;

/**
 * Trait ComputesWeaponSnapshot
 */
trait ComputesWeaponSnapshot
{
    /**
     * @param  array<int, array<string, mixed>>  $loadout
     * @return array{pilot_guns_count: int, turrets_manned_count: int, turrets_remote_count: int, turret_weapon_guns_count: int, missile_rack_count: int, missile_count: int, countermeasures_count: int}|null
     */
    public static function computeWeaponSnapshot(array $loadout): ?array
    {
        if ($loadout === []) {
            return null;
        }

        $service = app(WeaponSnapshotService::class);

        return $service->compute($loadout);
    }
}
