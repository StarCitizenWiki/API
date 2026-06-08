<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Concerns;

use Illuminate\Support\Arr;

/**
 * Maps equipment Type values to canonical display categories
 */
trait CategorizesEquipmentType
{
    /**
     * Return all known category labels in canonical display order.
     *
     * @return list<string>
     */
    public static function categoryOrder(): array
    {
        return [
            'Docked Vehicles',
            'Weapons',
            'Manned Turrets',
            'Remote Turrets',
            'PDC Turrets',
            'Turrets',
            'Missile & Bomb Racks',
            'Weapon Lockers',
            'Modules',
            'Mining & Salvage',
            'Tractor Beams',
            'Shields',
            'Coolers',
            'Power Plants',
            'Flight Controller',
            'Quantum Drives',
            'Counter Measures',
            'Cargo Grids',
            'Radars',
            'EMP',
            'QED',

            'Life Support',
            'Relays',
            'Fuel',

            'Controllers',
            'Crew Stations',
            'AI Modules',
            'Landing Systems',
            'Docking',
            'Thrusters',
            'Doors & Hatches',
            'Systems',
            'Customization',
            'Paints',
            'Armor',
            'Other',
        ];
    }

    /**
     * Categorize an equipment type string into a display label.
     *
     * Handles both primary types (e.g. "Shield") and dotted types (e.g. "Shield.UNDEFINED").
     * Falls back to "Other" for unknown types, with special handling for relay-only
     * types that don't appear in the regular loadout port list.
     *
     * @param  string  $type  Primary equipment type (before the dot).
     * @param  string|null  $subType  Sub-type after the dot (e.g. "MannedTurret").
     * @param  string|null  $className  Optional class name for turret disambiguation.
     * @return string Display category label.
     */
    protected function categorizeEquipmentType(string $type, ?string $subType = null, ?string $className = null): string
    {
        $category = match ($type) {
            'AIModule' => 'AI Modules',
            'AirTrafficController',
            'CapacitorAssignmentController',
            'CommsController',
            'CoolerController',
            'DoorController',
            'EnergyController',
            'FuelController',
            'LightController',
            'MissileController',
            'ShieldController',
            'WeaponController' => 'Controllers',
            'Armor' => 'Armor',
            'CargoGrid' => 'Cargo Grids',
            'Cooler' => 'Coolers',
            'Seat', 'SeatAccess', 'SeatDashboard' => 'Crew Stations',
            'DockingAnimator', 'DockingCollar' => 'Docking',
            'NOITEM_Vehicle' => 'Docked Vehicles',
            'Door' => 'Doors & Hatches',
            'EMP' => 'EMP',
            'FlightController' => 'Flight Controller',
            'FuelTank', 'QuantumFuelTank', 'FuelIntake' => 'Fuel',
            'LandingSystem' => 'Landing Systems',
            'LifeSupportGenerator' => 'Life Support',
            'MainThruster', 'ManneuverThruster' => 'Thrusters',
            'MissileLauncher', 'BombRack', 'BombLauncher', 'GroundVehicleMissileLauncher' => 'Missile & Bomb Racks',
            'Module' => 'Modules',
            'Paint' => 'Paints',
            'PowerPlant' => 'Power Plants',
            'QuantumDrive' => 'Quantum Drives',
            'QuantumInterdictionGenerator' => 'QED',
            'Radar' => 'Radars',
            'Relay' => 'Relays',
            'Shield' => 'Shields',
            'Computer', 'Display', 'SelfDestruct' => 'Systems',
            'ToolArm' => 'Mining & Salvage',
            'Turret', 'TurretBase', 'UtilityTurret' => 'Turrets',
            'TractorBeam' => 'Tractor Beams',
            'WeaponDefensive' => 'Counter Measures',
            'WeaponGun', 'WeaponMining' => 'Weapons',
            default => str_starts_with($type, 'Flair') ? 'Customization' : 'Other',
        };

        // Turret sub-type disambiguation
        if ($category === 'Turrets') {
            if ($className !== null && str_contains($className, 'Remote')) {
                $category = 'Remote Turrets';
            } elseif ($subType === 'MannedTurret') {
                $category = 'Manned Turrets';
            } elseif ($subType === 'PDCTurret') {
                $category = 'PDC Turrets';
            }
        }

        return $category;
    }

    /**
     * Categorize a raw loadout port array into a display category.
     *
     * @param  array<string, mixed>  $item  Raw port entry from loadout JSON.
     * @return string Display category label.
     */
    public function categorizeRawPort(array $item): string
    {
        [$type, $subtype] = explode('.', Arr::get($item, 'Type', '.'));

        $category = $this->categorizeEquipmentType(
            $type,
            $subtype,
            Arr::get($item, 'ClassName'),
        );

        if ($category === 'Other' && ($type === '' || $type === null)) {
            $hardpoint = Arr::get($item, 'HardpointName');

            if ($hardpoint !== null) {
                $fallback = $this->categorizeByHardpointName($hardpoint);

                if ($fallback !== 'Other') {
                    return $fallback;
                }
            }
        }

        if ($category === 'Docking' && array_any(Arr::get($item, 'Loadout') ?? [], fn($child) => $this->isAttachedVehiclePort($child) && is_string($uuid = Arr::get($child, 'UUID')) && $uuid !== '')) {
            return 'Docked Vehicles';
        }

        return $category;
    }

    /**
     * Check whether a raw port represents a NOITEM_Vehicle type.
     *
     * @param  array<string, mixed>  $port
     */
    protected function isAttachedVehiclePort(array $port): bool
    {
        $type = Arr::get($port, 'Type', '');
        $compatibleTypes = Arr::get($port, 'CompatibleTypes') ?? Arr::get($port, 'ItemTypes', []);

        return str_starts_with($type, 'NOITEM_Vehicle')
            || collect($compatibleTypes)->contains(fn (array $compatibleType): bool => ($compatibleType['Type'] ?? '') === 'NOITEM_Vehicle');
    }

    /**
     * Fallback categorization based on hardpoint naming patterns.
     *
     * Used when no enriched Type data is available (empty ports without installed items).
     * Labels match the type-based categories from categorizeEquipmentType().
     */
    protected function categorizeByHardpointName(string $hardpoint): string
    {
        return match (true) {
            str_starts_with($hardpoint, 'hardpoint_thruster_'),
            str_starts_with($hardpoint, 'hardpoint_engine_') => 'Thrusters',
            str_starts_with($hardpoint, 'hardpoint_turret_console_'),
            str_ends_with($hardpoint, '_seataccess'),
            str_ends_with($hardpoint, '_seat_access') => 'Crew Stations',
            str_starts_with($hardpoint, 'hardpoint_turret_'),
            str_starts_with($hardpoint, 'hardpoint_rear_turret_'),
            str_starts_with($hardpoint, 'hardpoint_front_turret_'),
            str_starts_with($hardpoint, 'hardpoint_left_turret'),
            str_starts_with($hardpoint, 'hardpoint_right_turret'),
            str_starts_with($hardpoint, 'hardpoint_nose_turret_'),
            str_starts_with($hardpoint, 'hardpoint_lower_turret_'),
            str_starts_with($hardpoint, 'hardpoint_upper_turret_') => 'Turrets',
            str_starts_with($hardpoint, 'hardpoint_seat_'),
            str_starts_with($hardpoint, 'hardpoint_dashboard_'),
            str_starts_with($hardpoint, 'hardpoint_brig_controller_') => 'Crew Stations',
            str_starts_with($hardpoint, 'hardpoint_controller_'),
            str_starts_with($hardpoint, 'hardpoint_ATC_') => 'Controllers',
            str_starts_with($hardpoint, 'hardpoint_door_') => 'Doors & Hatches',
            str_starts_with($hardpoint, 'hardpoint_countermeasures_') => 'Counter Measures',
            str_starts_with($hardpoint, 'hardpoint_cooler_') => 'Coolers',
            str_starts_with($hardpoint, 'hardpoint_shield_') => 'Shields',
            str_starts_with($hardpoint, 'hardpoint_power_plant') => 'Power Plants',
            str_starts_with($hardpoint, 'hardpoint_quantum_interdiction') => 'QED',
            str_starts_with($hardpoint, 'hardpoint_quantum_') => 'Quantum Drives',
            str_starts_with($hardpoint, 'hardpoint_fuel_') => 'Fuel',
            str_starts_with($hardpoint, 'hardpoint_missile_') => 'Missile & Bomb Racks',
            str_contains($hardpoint, '_module') => 'Modules',
            str_starts_with($hardpoint, 'hardpoint_weapon_regen_pool') => 'Systems',
            str_starts_with($hardpoint, 'hardpoint_weapon_locker_'),
            str_starts_with($hardpoint, 'hardpoint_rstairwell_weapon_locker'),
            str_starts_with($hardpoint, 'hardpoint_hangar_weapon_locker') => 'Weapon Lockers',
            str_starts_with($hardpoint, 'hardpoint_weapon_emp'),
            str_starts_with($hardpoint, 'hardpoint_weapon_rack') => 'Other',
            str_starts_with($hardpoint, 'hardpoint_weapon_mining'),
            str_starts_with($hardpoint, 'hardpoint_mining_') => 'Mining & Salvage',
            str_starts_with($hardpoint, 'hardpoint_weapon_') => 'Weapons',
            str_starts_with($hardpoint, 'hardpoint_tractor') => 'Tractor Beams',
            str_starts_with($hardpoint, 'hardpoint_salvage_'),
            str_starts_with($hardpoint, 'hardpoint_tool_') => 'Mining & Salvage',
            str_starts_with($hardpoint, 'hardpoint_computer_') => 'Systems',
            str_starts_with($hardpoint, 'hardpoint_paint') => 'Paints',
            str_starts_with($hardpoint, 'hardpoint_armor') => 'Armor',
            str_starts_with($hardpoint, 'hardpoint_landingpad_') => 'Landing Systems',
            str_starts_with($hardpoint, 'hardpoint_') => 'Other',
            default => 'Other',
        };
    }
}
