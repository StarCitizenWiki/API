<?php

declare(strict_types=1);

namespace App\Support\Game;

use App\Support\Format;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Compact two-line presenter for a single hardpoint port.
 *
 * Line 1: Size badge Name Right-aligned primary stat
 * Line 2: Secondary stats (power, coolant, DPS, range, etc.)
 */
final class HardpointRow
{
    /**
     * Simple stat mapping: type => [data_path, label, unit, icon].
     * Types not listed here fall through to the default (no primary stat).
     */
    private const array STAT_MAP = [
        'WeaponGun' => ['vehicle_weapon.damage.burst', 'DPS', '', 'crosshair'],
        'Shield' => ['shield.max_health', 'HP', '', 'shield'],
        'PowerPlant' => ['power_plant.power_segment_generation', 'Power Gen', '', 'zap'],
        'Cooler' => ['resource_network.generation.coolant', 'Cool Gen', '', 'fan'],
        'QuantumDrive' => ['quantum_drive.standard_jump.drive_speed', 'Speed', '', 'gauge'],
        'Radar' => ['radar.aim_assist.distance_max_assignment', 'Aim', 'm', 'wifi'],
        'Armor' => ['armor.health', 'HP', '', 'shield'],
        'CargoGrid' => ['inventory.scu', 'SCU', '', 'package'],
        'CountermeasureLauncher' => ['ammunition.capacity', 'Ammo', '', 'layers'],
        'WeaponDefensive' => ['ammunition.capacity', 'Ammo', '', 'layers'],
        'EMP' => ['emp.radius', 'Radius', '', null],
        'QuantumInterdictionGenerator' => ['quantum_interdiction_generator.range', 'Range', '', null],
    ];

    /**
     * Types whose power usage should be zeroed (they generate power, not consume it).
     */
    private const array ZERO_POWER_TYPES = ['PowerPlant'];

    /**
     * Types whose coolant usage should be zeroed (they generate coolant or weapon hides it).
     */
    private const array ZERO_COOLANT_TYPES = ['Cooler', 'WeaponGun'];

    /**
     * Build a compact row representation from a port array (PortResource output).
     *
     * @param  array<string, mixed>  $port
     * @param  array<string, mixed>  $powerPools  Power pool data keyed by item type
     * @param  int  $categoryIndex  Position of this port within its category group
     * @return array{
     *     name: string,
     *     display_name: string,
     *     size_label: string|null,
     *     size_min: int|null,
     *     size_max: int|null,
     *     type: string|null,
     *     sub_type: string|null,
     *     editable: bool,
     *     has_equipped_item: bool,
     *     equipped_item_uuid: string|null,
     *     equipped_item_link: string|null,
     *     primary_stat: float|int|null,
     *     primary_label: string|null,
     *     primary_unit: string,
     *     primary_icon: string|null,
     *     secondary_stats: list<string>,
     *     is_empty_port: bool,
     *     is_attached_vehicle: bool,
     *     attached_vehicle: array<string, mixed>|null,
     *     child_count: int,
     *     compatible_type: string|null,
     *     deactivated: bool,
     *     deactivation_reason: string|null,
     *     item_size: int|null,
     *     power_usage: float|int|null,
     *     coolant_usage: float|int|null,
     *     type_annotation: string|null,
     *     position: string|null,
     *     pilot_slaveable: bool,
     *     required_tags: array|null,
     *     port_tags: array|null,
     * }
     */
    public static function make(array $port, array $powerPools = [], int $categoryIndex = 0): array
    {
        $equippedItem = Arr::get($port, 'equipped_item') ?? Arr::get($port, 'equipped_port_item');
        $type = Arr::get($port, 'type');
        $subType = Arr::get($port, 'sub_type');
        $sizeMin = Arr::get($port, 'sizes.min');
        $sizeMax = Arr::get($port, 'sizes.max');
        $sizeLabel = self::formatSizeLabel($sizeMin, $sizeMax);
        $isAttachedVehicle = Arr::get($port, 'attached_vehicle') !== null;
        $isEmpty = $equippedItem === null && ! $isAttachedVehicle;

        $displayName = self::resolveDisplayName($port, $equippedItem, $isAttachedVehicle);
        $primaryStat = null;
        $primaryLabel = null;
        $primaryUnit = '';
        $primaryIcon = null;
        $secondaryStats = [];
        $itemSize = null;
        $powerUsage = null;
        $coolantUsage = null;

        if ($isAttachedVehicle) {
            $primaryStat = self::attachedVehiclePrimary(Arr::get($port, 'attached_vehicle'));
        } elseif (! $isEmpty) {
            $itemSize = Arr::get($equippedItem, 'size');
            $powerUsage = Arr::get($equippedItem, 'resource_network.usage.power.maximum');
            $coolantUsage = Arr::get($equippedItem, 'resource_network.usage.coolant.maximum');

            $stats = self::extractStats($type, $equippedItem, $port);
            $primaryStat = $stats['stat'];
            $primaryLabel = $stats['label'];
            $primaryUnit = $stats['unit'];
            $primaryIcon = $stats['icon'];
            $secondaryStats = $stats['secondaries'];

            if (in_array($type, self::ZERO_POWER_TYPES, true)) {
                $powerUsage = null;
            }

            if (in_array($type, self::ZERO_COOLANT_TYPES, true)) {
                $coolantUsage = null;
            }
        }

        $typeAnnotation = self::extractTypeAnnotation($equippedItem, $isEmpty);

        [$deactivated, $deactivationReason] = self::checkDeactivation(
            $equippedItem,
            $isEmpty,
            $powerPools,
            $categoryIndex,
        );

        return [
            'name' => Arr::get($port, 'name', '-'),
            'display_name' => $displayName,
            'size_label' => $sizeLabel,
            'size_min' => $sizeMin,
            'size_max' => $sizeMax,
            'type' => $type,
            'sub_type' => $subType,
            'editable' => (bool) Arr::get($port, 'editable', false),
            'has_equipped_item' => ! $isEmpty,
            'equipped_item_uuid' => Arr::get($port, 'equipped_item_uuid') ?? Arr::get($equippedItem, 'uuid'),
            'equipped_item_link' => Arr::get($equippedItem, 'link'),
            'primary_stat' => $primaryStat,
            'primary_label' => $primaryLabel,
            'primary_unit' => $primaryUnit,
            'primary_icon' => $primaryIcon,
            'secondary_stats' => $secondaryStats,
            'is_empty_port' => $isEmpty,
            'is_attached_vehicle' => $isAttachedVehicle,
            'attached_vehicle' => Arr::get($port, 'attached_vehicle'),
            'child_count' => count(Arr::get($port, 'ports') ?? []),
            'compatible_type' => self::extractCompatibleType($port),
            'deactivated' => $deactivated,
            'deactivation_reason' => $deactivationReason,
            'item_size' => $itemSize,
            'power_usage' => $powerUsage,
            'coolant_usage' => $coolantUsage,
            'type_annotation' => $typeAnnotation,
            'position' => Arr::get($port, 'position'),
            'pilot_slaveable' => (bool) Arr::get($port, 'pilot_slaveable', false),
            'required_tags' => Arr::get($port, 'required_tags') ?: null,
            'port_tags' => Arr::get($port, 'port_tags') ?: null,
        ];
    }

    /**
     * Check if an equipped item is deactivated due to power pool limits.
     *
     * @param  array<string, mixed>|null  $equippedItem
     * @param  array<string, mixed>  $powerPools
     * @return array{0: bool, 1: string|null}
     */
    private static function checkDeactivation(?array $equippedItem, bool $isEmpty, array $powerPools, int $categoryIndex): array
    {
        if ($isEmpty || empty($powerPools) || $equippedItem === null) {
            return [false, null];
        }

        $itemType = Arr::get($equippedItem, 'type');

        if ($itemType !== 'Shield') {
            return [false, null];
        }

        $pool = data_get($powerPools, $itemType);
        $poolSize = data_get($pool, 'size');

        if ($poolSize === null) {
            return [false, null];
        }

        if ($poolSize >= 0 && $categoryIndex >= $poolSize) {
            $idx = $categoryIndex + 1;

            return [true, "Pool Limit ({$idx} of {$poolSize} active)"];
        }

        return [false, null];
    }

    /**
     * Determine the display name for the port row.
     */
    private static function resolveDisplayName(array $port, ?array $item, bool $isAttachedVehicle): string
    {
        if ($isAttachedVehicle) {
            return Arr::get($port, 'attached_vehicle.name', 'Attached Vehicle');
        }

        $itemName = Arr::get($item, 'name');

        if ($itemName !== null && $itemName !== '' && $itemName !== '<= PLACEHOLDER =>') {
            return $itemName;
        }

        $portName = Arr::get($port, 'name', 'Port');

        return (string) Str::of($portName)->lower()->replace('hardpoint_', '')->headline();
    }

    /**
     * Format the size label (e.g. "S3" or "S2-S4").
     */
    private static function formatSizeLabel(?int $min, ?int $max): ?string
    {
        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null && $min === $max) {
            return 'S'.$min;
        }

        $parts = [];

        if ($min !== null) {
            $parts[] = 'S'.$min;
        }

        if ($max !== null) {
            $parts[] = 'S'.$max;
        }

        return implode('-', $parts);
    }

    /**
     * Extract the compatible type label for empty ports.
     */
    private static function extractCompatibleType(array $port): ?string
    {
        $types = Arr::get($port, 'compatible_types', []);

        if (empty($types)) {
            return null;
        }

        $first = Arr::get($types, '0.type');

        if ($first === null) {
            return null;
        }

        return (string) Str::of($first)->headline();
    }

    /**
     * Extract the type annotation string for an equipped item.
     * Shows weapon type, or class/grade for other items.
     */
    private static function extractTypeAnnotation(?array $equippedItem, bool $isEmpty): ?string
    {
        if ($isEmpty || $equippedItem === null) {
            return null;
        }

        $itemName = Arr::get($equippedItem, 'name');
        $hasNamedItem = ! empty($itemName) && $itemName !== '<= PLACEHOLDER =>';

        if (! $hasNamedItem) {
            return null;
        }

        $equippedItemType = Arr::get($equippedItem, 'type');
        $isWeapon = in_array($equippedItemType, ['WeaponGun', 'WeaponMining', 'WeaponPersonal'], true);

        if ($isWeapon) {
            $weaponType = Arr::get($equippedItem, 'vehicle_weapon.type');

            return $weaponType ?: null;
        }

        $classificationLabel = Arr::get($equippedItem, 'class');
        $grade = $classificationLabel ? Arr::get($equippedItem, 'grade') : null;
        $parts = array_filter([$classificationLabel, $grade]);

        return ! empty($parts) ? implode(' / ', $parts) : null;
    }

    /**
     * Extract stats for an equipped item. Uses STAT_MAP for simple lookups,
     * with dedicated methods for types that need secondaries or custom paths.
     *
     * @return array{stat: float|int|null, label: string|null, unit: string, icon: string|null, secondaries: list<string>}
     */
    private static function extractStats(?string $type, array $item, array $port): array
    {
        return match ($type) {
            'Shield' => self::shieldStats($item),
            'MissileLauncher', 'BombLauncher' => self::missileRackStats($port),
            'Missile', 'Bomb', 'Torpedo' => self::missileStats($item),
            'FlightController' => self::flightControllerStats($item),
            'WeaponDefensive' => self::counterMeasureStats($item),
            'WeaponGun' => self::weaponGunStats($item),
            default => self::mapLookup($type, $item),
        };
    }

    /**
     * Look up stats from STAT_MAP for types with simple single-value extraction.
     *
     * @return array{stat: float|int|null, label: string|null, unit: string, icon: string|null, secondaries: list<string>}
     */
    private static function mapLookup(?string $type, array $item): array
    {
        if ($type === null || ! isset(self::STAT_MAP[$type])) {
            return ['stat' => null, 'label' => null, 'unit' => '', 'icon' => null, 'secondaries' => []];
        }

        [$path, $label, $unit, $icon] = self::STAT_MAP[$type];

        return [
            'stat' => Arr::get($item, $path),
            'label' => $label,
            'unit' => $unit,
            'icon' => $icon,
            'secondaries' => [],
        ];
    }

    /**
     * Shield stats with regen secondary.
     *
     * @return array{stat: float|int|null, label: string, unit: string, icon: string, secondaries: list<string>}
     */
    private static function shieldStats(array $item): array
    {
        $secondaries = [];
        $regenRate = Arr::get($item, 'shield.regen_rate');

        if ($regenRate !== null) {
            $secondaries[] = Format::compact($regenRate, 0).'/s Regen';
        }

        $regenTime = Arr::get($item, 'shield.regen_time');
        if ($regenTime !== null) {
            $secondaries[] = Format::compact($regenTime, 0).'s Full';
        }

        return [
            'stat' => Arr::get($item, 'shield.max_health'),
            'label' => 'HP',
            'unit' => '',
            'icon' => 'shield',
            'secondaries' => $secondaries,
        ];
    }

    /**
     * Missile rack stats (child port count).
     *
     * @return array{stat: int, label: string, unit: string, icon: string, secondaries: list<string>}
     */
    private static function missileRackStats(array $port): array
    {
        return [
            'stat' => count(Arr::get($port, 'ports', [])),
            'label' => 'Missiles',
            'unit' => '',
            'icon' => 'arrow-up',
            'secondaries' => [],
        ];
    }

    /**
     * Missile/bomb/torpedo stats with signal type and range secondaries.
     *
     * @return array{stat: float|int|null, label: string, unit: string, icon: string, secondaries: list<string>}
     */
    private static function missileStats(array $item): array
    {
        $secondaries = [];
        $signalType = Arr::get($item, 'missile.signal_type');

        if ($signalType !== null) {
            $secondaries[] = (string) $signalType;
        }

        $rangeMax = Arr::get($item, 'missile.target_lock.range_max');

        if ($rangeMax !== null) {
            $secondaries[] = Format::compact($rangeMax, 0).'m';
        }

        return [
            'stat' => Arr::get($item, 'missile.damage_total'),
            'label' => 'Dmg',
            'unit' => '',
            'icon' => 'flame',
            'secondaries' => $secondaries,
        ];
    }

    /**
     * Counter measure stats with signature secondaries.
     *
     * @return array{stat: float|int|null, label: string, unit: string, icon: string, secondaries: list<string>}
     */
    private static function counterMeasureStats(array $item): array
    {
        $secondaries = [];
        $type = Arr::get($item, 'counter_measure.type');

        if ($type !== null) {
            $secondaries[] = (string) $type;
        }

        $signature = Arr::get($item, 'counter_measure.signature', []);
        $parts = [];

        if (($signature['infrared'] ?? 0) > 0) {
            $parts[] = 'IR '.Format::compact($signature['infrared'], 0);
        }

        if (($signature['cross_section'] ?? 0) > 0) {
            $parts[] = 'CS '.Format::compact($signature['cross_section'], 0);
        }

        if (($signature['electromagnetic'] ?? 0) > 0) {
            $parts[] = 'EM '.Format::compact($signature['electromagnetic'], 0);
        }

        if ($parts !== []) {
            $secondaries[] = implode(' / ', $parts);
        }

        return [
            'stat' => Arr::get($item, 'ammunition.capacity'),
            'label' => 'Ammo',
            'unit' => '',
            'icon' => 'layers',
            'secondaries' => $secondaries,
        ];
    }

    /**
     * Weapon gun stats with ammo secondary for finite-ammo weapons.
     *
     * @return array{stat: float|int|null, label: string, unit: string, icon: string, secondaries: list<string>}
     */
    private static function weaponGunStats(array $item): array
    {
        $secondaries = [];
        $capacity = Arr::get($item, 'ammunition.capacity');

        if ($capacity !== null && $capacity > 0) {
            $secondaries[] = Format::compact($capacity, 0).' rounds';
        }

        $alpha = Arr::get($item, 'vehicle_weapon.damage.alpha_total');

        if ($alpha !== null) {
            $secondaries[] = Format::compact($alpha, 1).' α';
        }

        return [
            'stat' => Arr::get($item, 'vehicle_weapon.damage.burst'),
            'label' => 'DPS',
            'unit' => '',
            'icon' => 'crosshair',
            'secondaries' => $secondaries,
        ];
    }

    /**
     * Flight controller stats with nav speed secondary.
     *
     * @return array{stat: float|int|null, label: string, unit: string, icon: string, secondaries: list<string>}
     */
    private static function flightControllerStats(array $item): array
    {
        $secondaries = [];
        $navSpeed = Arr::get($item, 'flight_controller.max_speed');

        if ($navSpeed !== null) {
            $secondaries[] = Format::compact($navSpeed, 0).'m/s Nav';
        }

        return [
            'stat' => Arr::get($item, 'flight_controller.scm_speed'),
            'label' => 'SCM',
            'unit' => 'm/s',
            'icon' => 'move',
            'secondaries' => $secondaries,
        ];
    }

    /**
     * Build the primary stat string for an attached vehicle.
     */
    private static function attachedVehiclePrimary(?array $vehicle): ?string
    {
        if ($vehicle === null) {
            return null;
        }

        $sizeClass = Arr::get($vehicle, 'size_class');
        $parts = [];

        if ($sizeClass !== null) {
            $parts[] = 'S'.$sizeClass;
        }

        if (Arr::get($vehicle, 'is_spaceship')) {
            $parts[] = 'Ship';
        } elseif (Arr::get($vehicle, 'is_gravlev')) {
            $parts[] = 'Gravlev';
        } elseif (Arr::get($vehicle, 'is_vehicle')) {
            $parts[] = 'Ground Vehicle';
        }

        return implode(' ', $parts) ?: null;
    }
}
