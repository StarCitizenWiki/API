<?php

declare(strict_types=1);

namespace App\Services\Game;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WeaponSnapshotService
{
    /**
     * Compute weapon statistics from vehicle loadout structure.
     *
     * @param  array<int, array<string, mixed>>  $loadout
     * @return array{
     *     pilot_guns_count: int,
     *     turrets_manned_count: int,
     *     turrets_remote_count: int,
     *     turret_weapon_guns_count: int,
     *     missile_rack_count: int,
     *     missile_count: int,
     *     countermeasures_count: int
     * }
     */
    public function compute(array $loadout): array
    {
        $counts = [
            'pilot_guns_count' => 0,
            'turrets_manned_count' => 0,
            'turrets_remote_count' => 0,
            'turret_weapon_guns_count' => 0,
            'missile_rack_count' => 0,
            'missile_count' => 0,
            'countermeasures_count' => 0,
        ];

        $this->traversePorts($loadout, [], $counts);

        return $counts;
    }

    /**
     * Recursively traverse ports and count weapon statistics.
     *
     * @param  array<int, array<string, mixed>>  $ports
     * @param  array<int, array<string, mixed>>  $turretAncestors
     * @param  array<string, int>  $counts
     */
    private function traversePorts(array $ports, array $turretAncestors, array &$counts): void
    {
        foreach ($ports as $port) {
            $type = $this->extractType($port);
            $subtype = $this->extractSubtype($port);
            $isGimbal = $this->isGimbal($port);

            $currentAncestors = $turretAncestors;
            if ($this->isMannedTurret($port) && ! $isGimbal) {
                $counts['turrets_manned_count']++;
                $currentAncestors[] = $port;
            } elseif ($this->isRemoteTurret($port) && ! $isGimbal) {
                $counts['turrets_remote_count']++;
                $currentAncestors[] = $port;
            }

            if ($type === 'WeaponGun') {
                if ($this->hasNonGimbalTurretAncestor($currentAncestors)) {
                    $counts['turret_weapon_guns_count']++;
                } else {
                    $counts['pilot_guns_count']++;
                }
            }

            if ($type === 'MissileLauncher') {
                $counts['missile_rack_count']++;
            }

            if ($type === 'Missile') {
                $counts['missile_count']++;
            }

            if ($type === 'WeaponDefensive' && $subtype === 'CountermeasureLauncher') {
                $counts['countermeasures_count']++;
            }

            $childLoadout = Arr::get($port, 'Loadout', []);
            if (is_array($childLoadout) && count($childLoadout) > 0) {
                $this->traversePorts($childLoadout, $currentAncestors, $counts);
            }
        }
    }

    /**
     * Check if port is a gimbal mount.
     */
    private function isGimbal(array $port): bool
    {
        $hardpointName = Arr::get($port, 'HardpointName', '');
        $className = Arr::get($port, 'ClassName', '');
        $subtype = $this->extractSubtype($port);

        return Str::contains(Str::lower($hardpointName), ['gimbal', 'mount_gimbal'])
            || Str::contains(Str::lower($className), ['gimbal', 'mount_gimbal'])
            || Str::contains(Str::lower($subtype), ['gimbal', 'mount_gimbal']);
    }

    /**
     * Check if port is a manned turret.
     */
    private function isMannedTurret(array $port): bool
    {
        $type = $this->extractType($port);
        $className = Arr::get($port, 'ClassName', '');
        $subtype = $this->extractSubtype($port);

        $isTurretType = in_array($type, ['Turret', 'TurretBase', 'UtilityTurret'], true);

        return $isTurretType && (
            Str::contains($className, 'Manned')
            || Str::contains($subtype, 'Manned')
        );
    }

    /**
     * Check if port is a remote turret.
     */
    private function isRemoteTurret(array $port): bool
    {
        $type = $this->extractType($port);
        $className = Arr::get($port, 'ClassName', '');

        $isTurretType = in_array($type, ['Turret', 'TurretBase', 'UtilityTurret'], true);

        if (! $isTurretType) {
            return false;
        }

        // If it's a turret and contains "Remote", it's remote
        if (Str::contains($className, 'Remote')) {
            return true;
        }

        // If it's a turret but not manned, it's remote
        return ! $this->isMannedTurret($port);
    }

    /**
     * Check if ancestors array contains a non-gimbal turret.
     *
     * @param  array<int, array<string, mixed>>  $ancestors
     */
    private function hasNonGimbalTurretAncestor(array $ancestors): bool
    {
        return count($ancestors) > 0;
    }

    /**
     * Extract Type from port's Type field (format: "Type.Subtype").
     */
    private function extractType(array $port): string
    {
        $typeField = Arr::get($port, 'Type', '');
        if (! is_string($typeField)) {
            return '';
        }

        $parts = explode('.', $typeField, 2);

        return $parts[0] ?? '';
    }

    /**
     * Extract Subtype from port's Type field (format: "Type.Subtype").
     */
    private function extractSubtype(array $port): string
    {
        $typeField = Arr::get($port, 'Type', '');
        if (! is_string($typeField)) {
            return '';
        }

        $parts = explode('.', $typeField, 2);

        return $parts[1] ?? '';
    }
}
