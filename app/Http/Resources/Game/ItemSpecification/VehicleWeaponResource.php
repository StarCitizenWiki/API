<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_weapon_damage_entry',
    title: 'Vehicle Weapon Damage Entry',
    description: 'Single damage component entry derived from Ammunition impact/detonation damage fields.',
    properties: [
        new OA\Property(property: 'type', description: 'Damage phase bucket.', type: 'string', example: 'impact', nullable: true),
        new OA\Property(property: 'name', description: 'Damage type name (lowercase).', type: 'string', example: 'physical', nullable: true),
        new OA\Property(property: 'damage', description: 'Damage value.', type: 'double', example: 11.5, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_mode',
    title: 'Vehicle Weapon Mode',
    description: 'Fire mode entry as emitted by the resource. Type-specific fields are only present when the mode type matches (e.g. beam fields only appear for type=beam).',
    properties: [
        new OA\Property(property: 'mode', description: 'Mode name.', type: 'string', example: 'Rapid', nullable: true),
        new OA\Property(property: 'localised', description: 'Localized label.', type: 'string', example: '[AUTO]', nullable: true),
        new OA\Property(property: 'type', description: 'Fire type.', type: 'string', example: 'rapid', nullable: true),
        new OA\Property(property: 'rpm', description: 'Rounds per minute.', type: 'double', example: 925, nullable: true),
        new OA\Property(property: 'rounds_per_minute', description: 'Deprecated: Use rpm.', type: 'double', example: 925, nullable: true, deprecated: true),
        new OA\Property(property: 'ammo_per_shot', description: 'Ammo consumed per shot.', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'pellets_per_shot', description: 'Pellets per shot.', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'damage_per_second', description: 'Damage per second.', type: 'double', example: 0, nullable: true),

        // Heat / wear (projectile modes)
        new OA\Property(property: 'heat_per_shot', description: 'Heat generated per shot (projectile modes).', type: 'double', nullable: true),
        new OA\Property(property: 'wear_per_shot', description: 'Durability lost per shot (projectile modes).', type: 'double', nullable: true),

        // Heat / wear (continuous / beam modes)
        new OA\Property(property: 'heat_per_second', description: 'Heat generated per second (beam / continuous-fire modes).', type: 'double', nullable: true),
        new OA\Property(property: 'wear_per_second', description: 'Durability lost per second (beam / continuous-fire modes).', type: 'double', nullable: true),

        // Rapid
        new OA\Property(property: 'fire_during_spin_up', description: 'Whether the weapon fires during barrel spin-up (rapid mode).', type: 'boolean', nullable: true),

        // Burst
        new OA\Property(property: 'shot_count', description: 'Number of shots per burst (burst mode).', type: 'integer', nullable: true),
        new OA\Property(property: 'cooldown_time', description: 'Cooldown time between bursts in seconds (burst mode).', type: 'double', nullable: true, x: ['suffix' => ' s']),

        // Sequence
        new OA\Property(property: 'sequence_mode', description: 'Sequence mode identifier (sequence mode).', type: 'string', nullable: true),

        // Beam (combat)
        new OA\Property(property: 'charge_up_time', description: 'Beam spool-up time in seconds (beam mode).', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'charge_down_time', description: 'Beam spool-down time in seconds (beam mode).', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'full_damage_range', description: 'Range at which full damage is applied (beam mode).', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'zero_damage_range', description: 'Range at which damage drops to zero (beam mode).', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'hit_type', description: 'Beam hit registration type (beam mode).', type: 'string', nullable: true),
        new OA\Property(property: 'hit_radius', description: 'Beam impact radius (beam / salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'min_energy_draw', description: 'Minimum power draw (beam / salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'max_energy_draw', description: 'Maximum power draw (beam / salvage mode).', type: 'double', nullable: true),

        // Healing beam
        new OA\Property(property: 'healing_mode', description: 'Healing mode identifier (healingbeam mode).', type: 'string', nullable: true),
        new OA\Property(property: 'healing_per_second', description: 'SCU healed per second (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'ammo_per_mscu', description: 'Ammo consumed per medical SCU (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'medical_ammo_type', description: 'Medical ammo type tag (healingbeam mode).', type: 'string', nullable: true),
        new OA\Property(property: 'external_healing', description: 'External healing mode (healingbeam mode).', type: 'string', nullable: true),
        new OA\Property(property: 'toggle', description: 'Toggle mode flag (healingbeam mode).', type: 'boolean', nullable: true),
        new OA\Property(property: 'max_distance', description: 'Maximum healing distance (healingbeam mode).', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'max_sensor_distance', description: 'Maximum sensor range for target detection (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'auto_dosage_modifier', description: 'Auto-dosage BDL modifier (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'healing_break_time', description: 'Time before healing breaks in seconds (healingbeam mode).', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'max_dose_for_auto_adjustment', description: 'Max dose for auto-adjustment (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'battery_drain_per_second', description: 'Battery drain per second (healingbeam mode).', type: 'double', nullable: true),

        // Salvage / Repair
        new OA\Property(property: 'material_efficiency', description: 'Material recovery rate (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'max_health_repair_rate', description: 'Max hull repair rate (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'max_damage_map_repair_rate', description: 'Max damage-map repair rate (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'health_to_ammo_ratio', description: 'Health restored per ammo unit (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'ramp_up_time', description: 'Beam ramp-up time in seconds (salvage mode).', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'ramp_down_time', description: 'Beam ramp-down time in seconds (salvage mode).', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'max_vehicle_damage_ratio', description: 'Max vehicle damage ratio (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'repaired_material_ratio', description: 'Ratio of repaired material (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'salvage_can_fire_on_full', description: 'Can fire when target is at full health (salvage mode).', type: 'boolean', nullable: true),
        new OA\Property(property: 'damage_threshold', description: 'Damage threshold for salvage operations (salvage mode).', type: 'double', nullable: true),

        // Collection beam (mining)
        new OA\Property(property: 'minimum_distance', description: 'Minimum mining distance (collectionbeam mode).', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'maximum_distance', description: 'Maximum mining distance (collectionbeam mode).', type: 'double', nullable: true, x: ['suffix' => ' m']),
        new OA\Property(property: 'beam_radius', description: 'Collection beam radius (collectionbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'collection_rate', description: 'Ore collection rate (collectionbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'energy_draw', description: 'Power consumption (collectionbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'mining_extractor_tag', description: 'Extractor classification tag (collectionbeam mode).', type: 'string', nullable: true),

        // Tractor beam
        new OA\Property(property: 'toggle_mode', description: 'Toggle mode flag (tractorbeam mode).', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_damage_types',
    title: 'Vehicle Weapon Damage Types',
    description: 'Damage values split by type.',
    properties: [
        new OA\Property(property: 'physical', description: 'Physical damage.', type: 'double', nullable: true),
        new OA\Property(property: 'energy', description: 'Energy damage.', type: 'double', nullable: true),
        new OA\Property(property: 'distortion', description: 'Distortion damage.', type: 'double', nullable: true),
        new OA\Property(property: 'thermal', description: 'Thermal damage.', type: 'double', nullable: true),
        new OA\Property(property: 'biochemical', description: 'Biochemical damage.', type: 'double', nullable: true),
        new OA\Property(property: 'stun', description: 'Stun damage.', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_damage',
    title: 'Vehicle Weapon Damage',
    description: 'Damage summary and per-type alpha breakdown.',
    properties: [
        new OA\Property(property: 'sustained_60s', description: 'Sustained damage over 60 seconds.', type: 'double', nullable: true),
        new OA\Property(property: 'burst', description: 'Burst damage.', type: 'double', nullable: true),
        new OA\Property(property: 'alpha_total', description: 'Total alpha damage per shot.', type: 'double', nullable: true),
        new OA\Property(property: 'max', description: 'Maximum damage per magazine.', type: 'double', nullable: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'alpha', ref: '#/components/schemas/vehicle_weapon_damage_types', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_spread',
    title: 'Vehicle Weapon Spread',
    properties: [
        new OA\Property(property: 'min', description: 'Minimum spread angle.', type: 'double', nullable: true, x: ['suffix' => ' °']),
        new OA\Property(property: 'max', description: 'Maximum spread angle.', type: 'double', nullable: true, x: ['suffix' => ' °']),
        new OA\Property(property: 'minimum', description: 'Deprecated: Use min.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'first_attack', description: 'Spread added on the first shot.', type: 'double', nullable: true, x: ['suffix' => ' °']),
        new OA\Property(property: 'per_attack', description: 'Spread added per subsequent shot.', type: 'double', nullable: true, x: ['suffix' => ' °']),
        new OA\Property(property: 'decay', description: 'Rate at which spread recovers between shots.', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_barrel_spin_time',
    title: 'Vehicle Weapon Barrel Spin Time',
    properties: [
        new OA\Property(property: 'up', description: 'Spin-up time in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'down', description: 'Spin-down time in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_heat',
    title: 'Vehicle Weapon Heat',
    properties: [
        new OA\Property(property: 'per_shot', description: 'Heat generated per shot.', type: 'double', nullable: true),
        new OA\Property(property: 'cooling_delay', description: 'Delay before cooling begins in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'cooling_per_second', description: 'Cooling rate per second.', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_max_shots', description: 'Number of shots to trigger overheat.', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_max_time', description: 'Time to trigger overheat in seconds.', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_cooldown', description: 'Overheat recovery time in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_capacitor',
    title: 'Vehicle Weapon Capacitor',
    properties: [
        new OA\Property(property: 'max_ammo_load', description: 'Maximum capacitor ammo load.', type: 'double', nullable: true),
        new OA\Property(property: 'regen_per_second', description: 'Capacitor regeneration per second.', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown', description: 'Capacitor cooldown in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'requested_ammo_load', description: 'Requested ammo load from capacitor.', type: 'double', nullable: true),
        new OA\Property(property: 'costs_per_shot', description: 'Capacitor cost per shot.', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_charge',
    title: 'Vehicle Weapon Charge',
    properties: [
        new OA\Property(property: 'time', description: 'Time to reach full charge in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'overcharge_time', description: 'Overcharge window in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'overcharged_time', description: 'Duration of overcharged state in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'cooldown_time', description: 'Cooldown after firing in seconds.', type: 'double', nullable: true, x: ['suffix' => ' s']),
        new OA\Property(property: 'auto_fire', description: 'Auto-fire when fully charged.', type: 'boolean', nullable: true),
        new OA\Property(property: 'require_full_charge', description: 'Must be fully charged before firing.', type: 'boolean', nullable: true),
        new OA\Property(property: 'auto_charge', description: 'Auto-charges when held.', type: 'boolean', nullable: true),
        new OA\Property(property: 'interpolate_bonus', description: 'Interpolates charge bonus linearly.', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_charge_modifier',
    title: 'Vehicle Weapon Charge Modifier',
    properties: [
        new OA\Property(property: 'damage', description: 'Damage multiplier at full charge.', type: 'double', nullable: true),
        new OA\Property(property: 'fire_rate', description: 'Fire rate multiplier at full charge.', type: 'double', nullable: true),
        new OA\Property(property: 'ammo_speed', description: 'Projectile speed multiplier at full charge.', type: 'double', nullable: true),
        new OA\Property(property: 'fire_rate_override', description: 'Override fire rate at full charge.', type: 'double', nullable: true),
        new OA\Property(property: 'pellets_override', description: 'Override pellet count at full charge.', type: 'integer', nullable: true),
        new OA\Property(property: 'burst_shots_override', description: 'Override burst shot count at full charge.', type: 'integer', nullable: true),
        new OA\Property(property: 'heat_multiplier', description: 'Heat generation multiplier at full charge.', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon',
    title: 'Vehicle Weapon',
    description: 'Vehicle weapon stats including damage, fire modes, spread, heat, and capacitor data.',
    properties: [
        new OA\Property(
            property: 'class',
            description: 'Weapon class.',
            type: 'string',
            example: 'LaserCannon',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            description: 'Item type.',
            type: 'string',
            example: 'Weapon',
            nullable: true
        ),
        new OA\Property(property: 'capacity', description: 'Ammunition capacity.', type: 'integer', example: 50, nullable: true),
        new OA\Property(property: 'range', description: 'Effective range in meters.', type: 'double', example: 1800, nullable: true, x: ['suffix' => ' m']),

        new OA\Property(
            property: 'rpm',
            description: 'Primary mode rounds per minute.',
            type: 'double',
            example: 400,
            nullable: true
        ),

        new OA\Property(
            property: 'damage',
            ref: '#/components/schemas/vehicle_weapon_damage',
            description: 'Damage summary and per-type alpha/dps.',
            nullable: true
        ),

        new OA\Property(
            property: 'modes',
            description: 'Fire modes as provided by the game data.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_weapon_mode'),
            nullable: true
        ),

        // Deprecated / backward-compatibility fields
        new OA\Property(
            property: 'damage_per_shot',
            description: 'Deprecated. Use `damage.alpha_total` (and/or `damage.alpha.*` for per-type values).',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'damages',
            description: 'Deprecated. Use `damage` (and its `alpha`/`dps` breakdown).',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_weapon_damage_entry'),
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'regeneration',
            description: 'Deprecated. Use `capacitor.regen_per_second`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'ammunition',
            description: 'Deprecated: use ammunition from the root resource.',
            type: 'object',
            nullable: true,
            deprecated: true
        ),

        // Conditional blocks (present only when source fields exist)
        new OA\Property(property: 'spread', ref: '#/components/schemas/vehicle_weapon_spread', nullable: true),
        new OA\Property(property: 'barrel_spin_time', ref: '#/components/schemas/vehicle_weapon_barrel_spin_time', nullable: true),
        new OA\Property(property: 'heat', ref: '#/components/schemas/vehicle_weapon_heat', nullable: true),
        new OA\Property(property: 'capacitor', ref: '#/components/schemas/vehicle_weapon_capacitor', nullable: true),
        new OA\Property(property: 'charge', ref: '#/components/schemas/vehicle_weapon_charge', nullable: true),
        new OA\Property(property: 'charge_modifier', ref: '#/components/schemas/vehicle_weapon_charge_modifier', nullable: true),

        new OA\Property(
            property: 'magazine_volume',
            description: 'Total cargo volume consumed by a full magazine of ammunition. Derived from capacity x conversion rate.',
            properties: [
                new OA\Property(property: 'micro_scu', description: 'Volume in microSCU.', type: 'integer', example: 574560, nullable: true, x: ['suffix' => ' µSCU']),
                new OA\Property(property: 'scu', description: 'Volume in SCU.', type: 'double', example: 0.574560, nullable: true, x: ['suffix' => ' SCU']),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class VehicleWeaponResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $ammo = $this->extractFromStdItem($this->resource, 'Ammunition');
        $weapon = $this->extractFromStdItem($this->resource, 'Weapon');
        $mode = $weapon['Modes'][0] ?? null;
        $heat = $weapon['Heat'] ?? null;

        $impactDamage = $ammo['ImpactDamage'] ?? null;
        $detonationDamage = $ammo['DetonationDamage'] ?? null;

        $damages = array_filter([
            ['type' => 'impact', 'name' => 'physical', 'damage' => $impactDamage['Physical'] ?? null],
            ['type' => 'impact', 'name' => 'energy', 'damage' => $impactDamage['Energy'] ?? null],
            ['type' => 'impact', 'name' => 'distortion', 'damage' => $impactDamage['Distortion'] ?? null],
            ['type' => 'impact', 'name' => 'thermal', 'damage' => $impactDamage['Thermal'] ?? null],
            ['type' => 'impact', 'name' => 'biochemical', 'damage' => $impactDamage['Biochemical'] ?? null],
            ['type' => 'impact', 'name' => 'stun', 'damage' => $impactDamage['Stun'] ?? null],

            ['type' => 'detonation', 'name' => 'physical', 'damage' => $detonationDamage['Physical'] ?? null],
            ['type' => 'detonation', 'name' => 'energy', 'damage' => $detonationDamage['Energy'] ?? null],
            ['type' => 'detonation', 'name' => 'distortion', 'damage' => $detonationDamage['Distortion'] ?? null],
            ['type' => 'detonation', 'name' => 'thermal', 'damage' => $detonationDamage['Thermal'] ?? null],
            ['type' => 'detonation', 'name' => 'biochemical', 'damage' => $detonationDamage['Biochemical'] ?? null],
            ['type' => 'detonation', 'name' => 'stun', 'damage' => $detonationDamage['Stun'] ?? null],
        ], static fn (array $entry) => $entry !== [] && ! empty($entry['damage']));

        $modes = array_map(static function (array $mode): array {
            $charge = $mode['Charge'] ?? null;

            return [
                'mode' => $mode['Name'] ?? null,
                'localised' => $mode['LocalisedName'] ?? null,
                'type' => $mode['FireType'] ?? null,
                'rpm' => $mode['RoundsPerMinute'] ?? null,
                'rounds_per_minute' => $mode['RoundsPerMinute'] ?? null,
                'ammo_per_shot' => $mode['AmmoPerShot'] ?? null,
                'pellets_per_shot' => $mode['PelletsPerShot'] ?? null,
                'damage_per_second' => $mode['DamagePerSecond'] ?? null,

                'heat_per_shot' => $mode['HeatPerShot'] ?? null,
                'wear_per_shot' => $mode['WearPerShot'] ?? null,

                'heat_per_second' => $mode['HeatPerSecond'] ?? null,
                'wear_per_second' => $mode['WearPerSecond'] ?? null,

                'fire_during_spin_up' => $mode['FireDuringSpinUp'] ?? null,

                'shot_count' => $mode['ShotCount'] ?? null,
                'cooldown_time' => $mode['CooldownTime'] ?? null,

                'sequence_mode' => $mode['SequenceMode'] ?? null,

                'charge_up_time' => $charge['ChargeUpTime'] ?? null,
                'charge_down_time' => $charge['ChargeDownTime'] ?? null,
                'full_damage_range' => $charge['FullDamageRange'] ?? null,
                'zero_damage_range' => $charge['ZeroDamageRange'] ?? null,
                'hit_type' => $charge['HitType'] ?? null,
                'hit_radius' => $charge['HitRadius'] ?? null,
                'min_energy_draw' => $charge['MinEnergyDraw'] ?? null,
                'max_energy_draw' => $charge['MaxEnergyDraw'] ?? null,

                'healing_mode' => $mode['HealingMode'] ?? null,
                'healing_per_second' => $mode['HealingPerSecond'] ?? null,
                'ammo_per_mscu' => $mode['AmmoPerMSCU'] ?? null,
                'medical_ammo_type' => $mode['MedicalAmmoType'] ?? null,
                'external_healing' => $mode['ExternalHealing'] ?? null,
                'toggle' => $mode['Toggle'] ?? null,
                'max_distance' => $mode['MaxDistance'] ?? null,
                'max_sensor_distance' => $mode['MaxSensorDistance'] ?? null,
                'auto_dosage_modifier' => $mode['AutoDosageModifier'] ?? null,
                'healing_break_time' => $mode['HealingBreakTime'] ?? null,
                'max_dose_for_auto_adjustment' => $mode['MaxDoseForAutoAdjustment'] ?? null,
                'battery_drain_per_second' => $mode['BatteryDrainPerSecond'] ?? null,

                'material_efficiency' => $mode['MaterialEfficiency'] ?? null,
                'max_health_repair_rate' => $mode['MaxHealthRepairRate'] ?? null,
                'max_damage_map_repair_rate' => $mode['MaxDamageMapRepairRate'] ?? null,
                'health_to_ammo_ratio' => $mode['HealthToAmmoRatio'] ?? null,
                'ramp_up_time' => $mode['RampUpTime'] ?? null,
                'ramp_down_time' => $mode['RampDownTime'] ?? null,
                'max_vehicle_damage_ratio' => $mode['MaxVehicleDamageRatio'] ?? null,
                'repaired_material_ratio' => $mode['RepairedMaterialRatio'] ?? null,
                'salvage_can_fire_on_full' => $mode['SalvageCanFireOnFull'] ?? null,
                'damage_threshold' => $mode['DamageThreshold'] ?? null,

                'minimum_distance' => $mode['MinimumDistance'] ?? null,
                'maximum_distance' => $mode['MaximumDistance'] ?? null,
                'beam_radius' => $mode['BeamRadius'] ?? null,
                'collection_rate' => $mode['CollectionRate'] ?? null,
                'energy_draw' => $mode['EnergyDraw'] ?? null,
                'mining_extractor_tag' => $mode['MiningExtractorTag'] ?? null,

                'toggle_mode' => $mode['ToggleMode'] ?? null,
            ];
        }, $weapon['Modes'] ?? []);

        $weaponDamage = $weapon['Damage'] ?? null;
        $capacitor = $weapon['Capacitor'] ?? null;

        $result = [
            'class' => $weapon['WeaponClass'] ?? null,
            'type' => $this->extractFromStdItem($this->resource, 'DescriptionData.Item Type'),
            'capacity' => $ammo['Capacity'] ?? null,
            'range' => $weapon['EffectiveRange'] ?? null,

            'damage_per_shot' => $mode['Alpha'] ?? null,
            'regeneration' => $capacitor['MaxRegenPerSec'] ?? null,

            'rpm' => $mode['RoundsPerMinute'] ?? null,

            'damages' => $damages,
            'modes' => $modes,

            'damage' => [
                'sustained_60s' => $weaponDamage['Sustained60s'] ?? $weaponDamage['Sustained'] ?? null,
                'burst' => $weaponDamage['Burst'] ?? null,
                'alpha_total' => $weaponDamage['AlphaTotal'] ?? null,
                'max' => $weaponDamage['Maximum'] ?? null,
                'maximum' => $weaponDamage['Maximum'] ?? null,
                'dps' => [
                    'physical' => $mode['DpsPhysical'] ?? null,
                    'energy' => $mode['DpsEnergy'] ?? null,
                    'distortion' => $mode['DpsDistortion'] ?? null,
                    'thermal' => $mode['DpsThermal'] ?? null,
                    'biochemical' => $mode['DpsBiochemical'] ?? null,
                    'stun' => $mode['DpsStun'] ?? null,
                ],
                'alpha' => [
                    'physical' => $mode['AlphaPhysical'] ?? null,
                    'energy' => $mode['AlphaEnergy'] ?? null,
                    'distortion' => $mode['AlphaDistortion'] ?? null,
                    'thermal' => $mode['AlphaThermal'] ?? null,
                    'biochemical' => $mode['AlphaBiochemical'] ?? null,
                    'stun' => $mode['AlphaStun'] ?? null,
                ],
            ],

            $this->mergeWhen(isset($mode['Spread']), [
                'spread' => [
                    'min' => $mode['Spread']['Minimum'] ?? null,
                    'max' => $mode['Spread']['Maximum'] ?? null,
                    'minimum' => $mode['Spread']['Minimum'] ?? null,
                    'maximum' => $mode['Spread']['Maximum'] ?? null,
                    'first_attack' => $mode['Spread']['FirstAttack'] ?? null,
                    'per_attack' => $mode['Spread']['Attack'] ?? null,
                    'decay' => $mode['Spread']['Decay'] ?? null,
                ],
            ]),

            $this->mergeWhen(isset($mode['BarrelSpinTime']), [
                'barrel_spin_time' => [
                    'up' => $mode['BarrelSpinTime']['Up'] ?? null,
                    'down' => $mode['BarrelSpinTime']['Down'] ?? null,
                ],
            ]),

            $this->mergeWhen(! empty($heat), [
                'heat' => [
                    'per_shot' => $heat['HeatPerShot'] ?? null,
                    'cooling_delay' => $heat['CoolingDelay'] ?? null,
                    'cooling_per_second' => $heat['CoolingPerSecond'] ?? null,
                    'overheat_max_shots' => $heat['ShotsToOverheat'] ?? null,
                    'overheat_max_time' => $heat['TimeToOverheat'] ?? null,
                    'overheat_cooldown' => $heat['OverheatFixTime'] ?? null,
                ],
            ]),

            $this->mergeWhen(isset($capacitor['MaxAmmoLoad']), [
                'capacitor' => [
                    'max_ammo_load' => $capacitor['MaxAmmoLoad'] ?? null,
                    'regen_per_second' => $capacitor['MaxRegenPerSec'] ?? null,
                    'cooldown' => $capacitor['Cooldown'] ?? null,

                    'requested_ammo_load' => $capacitor['RequestedAmmoLoad'] ?? null,
                    'costs_per_shot' => $capacitor['CostPerBullet'] ?? null,
                ],
            ]),

            $this->mergeWhen(isset($mode['Charge']), [
                'charge' => [
                    'time' => $mode['Charge']['ChargeTime'] ?? null,
                    'overcharge_time' => $mode['Charge']['OverchargeTime'] ?? null,
                    'overcharged_time' => $mode['Charge']['OverchargedTime'] ?? null,
                    'cooldown_time' => $mode['Charge']['CooldownTime'] ?? null,
                    'auto_fire' => $mode['Charge']['AutoFire'] ?? null,
                    'require_full_charge' => $mode['Charge']['RequireFullCharge'] ?? null,
                    'auto_charge' => $mode['Charge']['AutoCharge'] ?? null,
                    'interpolate_bonus' => $mode['Charge']['InterpolateBonus'] ?? null,
                ],
                'charge_modifier' => [
                    'damage' => $mode['ChargeModifier']['Damage'] ?? null,
                    'fire_rate' => $mode['ChargeModifier']['FireRate'] ?? null,
                    'ammo_speed' => $mode['ChargeModifier']['AmmoSpeed'] ?? null,
                    'fire_rate_override' => $mode['ChargeModifier']['FireRateOverride'] ?? null,
                    'pellets_override' => $mode['ChargeModifier']['PelletsOverride'] ?? null,
                    'burst_shots_override' => $mode['ChargeModifier']['BurstShotsOverride'] ?? null,
                    'heat_multiplier' => $mode['ChargeModifier']['HeatMultiplier'] ?? null,
                ],
            ]),

            'ammunition' => new AmmunitionResource($this->resource),
        ];

        $capacity = $ammo['Capacity'] ?? null;
        $conversionRate = $ammo['ConversionRateMicroScu'] ?? null;

        if ($capacity !== null && $conversionRate !== null) {
            $totalMicroScu = (int) $capacity * (int) $conversionRate;
            $result['magazine_volume'] = [
                'micro_scu' => $totalMicroScu,
                'scu' => round($totalMicroScu / 1_000_000, 6),
            ];
        }

        return $result;
    }
}
