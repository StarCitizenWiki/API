<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_weapon_damage_entry',
    title: 'Vehicle Weapon Damage Entry',
    description: 'Single damage component entry derived from Ammunition impact/detonation damage fields.',
    properties: [
        new OA\Property(property: 'type', description: 'Damage phase bucket.', type: 'string', example: 'impact', nullable: true),
        new OA\Property(property: 'name', description: 'Damage type name (lowercase).', type: 'string', example: 'physical', nullable: true),
        new OA\Property(property: 'damage', type: 'double', example: 11.5, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_mode',
    title: 'Vehicle Weapon Mode',
    description: 'Fire mode entry as emitted by the resource. Type-specific fields are only present when the mode type matches (e.g. beam fields only appear for type=beam).',
    properties: [
        new OA\Property(property: 'mode', type: 'string', example: 'Rapid', nullable: true),
        new OA\Property(property: 'localised', type: 'string', example: '[AUTO]', nullable: true),
        new OA\Property(property: 'type', type: 'string', example: 'rapid', nullable: true),
        new OA\Property(property: 'rpm', type: 'double', example: 925, nullable: true),
        new OA\Property(property: 'rounds_per_minute', description: 'Deprecated: Use rpm.', type: 'double', example: 925, nullable: true, deprecated: true),
        new OA\Property(property: 'ammo_per_shot', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'pellets_per_shot', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'damage_per_second', type: 'double', example: 0, nullable: true),

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
        new OA\Property(property: 'cooldown_time', description: 'Cooldown time between bursts in seconds (burst mode).', type: 'double', nullable: true),

        // Sequence
        new OA\Property(property: 'sequence_mode', description: 'Sequence mode identifier (sequence mode).', type: 'string', nullable: true),

        // Beam (combat)
        new OA\Property(property: 'charge_up_time', description: 'Beam spool-up time in seconds (beam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'charge_down_time', description: 'Beam spool-down time in seconds (beam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'full_damage_range', description: 'Range at which full damage is applied (beam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'zero_damage_range', description: 'Range at which damage drops to zero (beam mode).', type: 'double', nullable: true),
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
        new OA\Property(property: 'max_distance', description: 'Maximum healing distance (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'max_sensor_distance', description: 'Maximum sensor range for target detection (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'auto_dosage_modifier', description: 'Auto-dosage BDL modifier (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'healing_break_time', description: 'Time before healing breaks in seconds (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'max_dose_for_auto_adjustment', description: 'Max dose for auto-adjustment (healingbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'battery_drain_per_second', description: 'Battery drain per second (healingbeam mode).', type: 'double', nullable: true),

        // Salvage / Repair
        new OA\Property(property: 'material_efficiency', description: 'Material recovery rate (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'max_health_repair_rate', description: 'Max hull repair rate (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'max_damage_map_repair_rate', description: 'Max damage-map repair rate (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'health_to_ammo_ratio', description: 'Health restored per ammo unit (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'ramp_up_time', description: 'Beam ramp-up time in seconds (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'ramp_down_time', description: 'Beam ramp-down time in seconds (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'max_vehicle_damage_ratio', description: 'Max vehicle damage ratio (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'repaired_material_ratio', description: 'Ratio of repaired material (salvage mode).', type: 'double', nullable: true),
        new OA\Property(property: 'salvage_can_fire_on_full', description: 'Can fire when target is at full health (salvage mode).', type: 'boolean', nullable: true),
        new OA\Property(property: 'damage_threshold', description: 'Damage threshold for salvage operations (salvage mode).', type: 'double', nullable: true),

        // Collection beam (mining)
        new OA\Property(property: 'minimum_distance', description: 'Minimum mining distance (collectionbeam mode).', type: 'double', nullable: true),
        new OA\Property(property: 'maximum_distance', description: 'Maximum mining distance (collectionbeam mode).', type: 'double', nullable: true),
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
        new OA\Property(property: 'physical', type: 'double', nullable: true),
        new OA\Property(property: 'energy', type: 'double', nullable: true),
        new OA\Property(property: 'distortion', type: 'double', nullable: true),
        new OA\Property(property: 'thermal', type: 'double', nullable: true),
        new OA\Property(property: 'biochemical', type: 'double', nullable: true),
        new OA\Property(property: 'stun', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_damage',
    title: 'Vehicle Weapon Damage',
    description: 'Damage summary block from stdItem.Weapon plus per-type alpha and dps from the primary mode.',
    properties: [
        new OA\Property(property: 'sustained_60s', type: 'double', nullable: true),
        new OA\Property(property: 'burst', type: 'double', nullable: true),
        new OA\Property(property: 'alpha_total', type: 'double', nullable: true),
        new OA\Property(property: 'max', type: 'double', nullable: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'alpha', ref: '#/components/schemas/vehicle_weapon_damage_types', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_spread',
    title: 'Vehicle Weapon Spread',
    properties: [
        new OA\Property(property: 'min', type: 'double', nullable: true),
        new OA\Property(property: 'max', type: 'double', nullable: true),
        new OA\Property(property: 'minimum', description: 'Deprecated: Use min.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', nullable: true, deprecated: true),
        new OA\Property(property: 'first_attack', type: 'double', nullable: true),
        new OA\Property(property: 'per_attack', type: 'double', nullable: true),
        new OA\Property(property: 'decay', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_barrel_spin_time',
    title: 'Vehicle Weapon Barrel Spin Time',
    properties: [
        new OA\Property(property: 'up', type: 'double', nullable: true),
        new OA\Property(property: 'down', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_heat',
    title: 'Vehicle Weapon Heat',
    properties: [
        new OA\Property(property: 'per_shot', type: 'double', nullable: true),
        new OA\Property(property: 'cooling_delay', type: 'double', nullable: true),
        new OA\Property(property: 'cooling_per_second', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_max_shots', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_max_time', type: 'double', nullable: true),
        new OA\Property(property: 'overheat_cooldown', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_capacitor',
    title: 'Vehicle Weapon Capacitor',
    properties: [
        new OA\Property(property: 'max_ammo_load', type: 'double', nullable: true),
        new OA\Property(property: 'regen_per_second', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown', type: 'double', nullable: true),
        new OA\Property(property: 'requested_ammo_load', type: 'double', nullable: true),
        new OA\Property(property: 'costs_per_shot', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'vehicle_weapon_charge',
    title: 'Vehicle Weapon Charge',
    properties: [
        new OA\Property(property: 'time', type: 'double', nullable: true),
        new OA\Property(property: 'overcharge_time', type: 'double', nullable: true),
        new OA\Property(property: 'overcharged_time', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown_time', type: 'double', nullable: true),
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
        new OA\Property(property: 'damage', type: 'double', nullable: true),
        new OA\Property(property: 'fire_rate', type: 'double', nullable: true),
        new OA\Property(property: 'ammo_speed', type: 'double', nullable: true),
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
    description: 'Vehicle weapon stats derived from stdItem.Weapon and stdItem.Ammunition. Conditional blocks (spread, barrel_spin_time, heat, capacitor, charge, charge_modifier) may be omitted when source data is absent.',
    properties: [
        new OA\Property(
            property: 'class',
            description: 'Weapon class from stdItem.Weapon.WeaponClass.',
            type: 'string',
            example: 'LaserCannon',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            description: 'Item type from DescriptionData.Item Type.',
            type: 'string',
            example: 'Weapon',
            nullable: true
        ),
        new OA\Property(property: 'capacity', description: 'Ammunition capacity (stdItem.Ammunition.Capacity).', type: 'integer', example: 50, nullable: true),
        new OA\Property(property: 'range', description: 'Effective range in meters (stdItem.Weapon.EffectiveRange).', type: 'double', example: 1800, nullable: true),

        new OA\Property(
            property: 'rpm',
            description: 'Primary mode rounds per minute (Modes.0.RoundsPerMinute).',
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
    ],
    type: 'object'
)]
class VehicleWeaponResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $ammo = $this->extractFromStdItem($this->resource, 'Ammunition');
        $weapon = $this->extractFromStdItem($this->resource, 'Weapon');
        $mode = Arr::get($weapon, 'Modes.0');
        $heat = Arr::get($weapon, 'Heat');

        $damages = array_filter([
            ['type' => 'impact', 'name' => 'physical', 'damage' => Arr::get($ammo, 'ImpactDamage.Physical')],
            ['type' => 'impact', 'name' => 'energy', 'damage' => Arr::get($ammo, 'ImpactDamage.Energy')],
            ['type' => 'impact', 'name' => 'distortion', 'damage' => Arr::get($ammo, 'ImpactDamage.Distortion')],
            ['type' => 'impact', 'name' => 'thermal', 'damage' => Arr::get($ammo, 'ImpactDamage.Thermal')],
            ['type' => 'impact', 'name' => 'biochemical', 'damage' => Arr::get($ammo, 'ImpactDamage.Biochemical')],
            ['type' => 'impact', 'name' => 'stun', 'damage' => Arr::get($ammo, 'ImpactDamage.Stun')],

            ['type' => 'detonation', 'name' => 'physical', 'damage' => Arr::get($ammo, 'DetonationDamage.Physical')],
            ['type' => 'detonation', 'name' => 'energy', 'damage' => Arr::get($ammo, 'DetonationDamage.Energy')],
            ['type' => 'detonation', 'name' => 'distortion', 'damage' => Arr::get($ammo, 'DetonationDamage.Distortion')],
            ['type' => 'detonation', 'name' => 'thermal', 'damage' => Arr::get($ammo, 'DetonationDamage.Thermal')],
            ['type' => 'detonation', 'name' => 'biochemical', 'damage' => Arr::get($ammo, 'DetonationDamage.Biochemical')],
            ['type' => 'detonation', 'name' => 'stun', 'damage' => Arr::get($ammo, 'DetonationDamage.Stun')],
        ], static fn (array $entry) => $entry !== [] && ! empty($entry['damage']));

        $modes = collect(Arr::get($weapon, 'Modes', []))
            ->map(static fn (mixed $mode): array => [
                'mode' => Arr::get($mode, 'Name'),
                'localised' => Arr::get($mode, 'LocalisedName'),
                'type' => Arr::get($mode, 'FireType'),
                'rpm' => Arr::get($mode, 'RoundsPerMinute'),
                'rounds_per_minute' => Arr::get($mode, 'RoundsPerMinute'),  // deprecated: use rpm
                'ammo_per_shot' => Arr::get($mode, 'AmmoPerShot'),
                'pellets_per_shot' => Arr::get($mode, 'PelletsPerShot'),
                'damage_per_second' => Arr::get($mode, 'DamagePerSecond'),

                // Heat / wear (projectile)
                'heat_per_shot' => Arr::get($mode, 'HeatPerShot'),
                'wear_per_shot' => Arr::get($mode, 'WearPerShot'),

                // Heat / wear (continuous / beam)
                'heat_per_second' => Arr::get($mode, 'HeatPerSecond'),
                'wear_per_second' => Arr::get($mode, 'WearPerSecond'),

                // Rapid
                'fire_during_spin_up' => Arr::get($mode, 'FireDuringSpinUp'),

                // Burst
                'shot_count' => Arr::get($mode, 'ShotCount'),
                'cooldown_time' => Arr::get($mode, 'CooldownTime'),

                // Sequence
                'sequence_mode' => Arr::get($mode, 'SequenceMode'),

                // Beam (combat)
                'charge_up_time' => Arr::get($mode, 'ChargeUpTime'),
                'charge_down_time' => Arr::get($mode, 'ChargeDownTime'),
                'full_damage_range' => Arr::get($mode, 'FullDamageRange'),
                'zero_damage_range' => Arr::get($mode, 'ZeroDamageRange'),
                'hit_type' => Arr::get($mode, 'HitType'),
                'hit_radius' => Arr::get($mode, 'HitRadius'),
                'min_energy_draw' => Arr::get($mode, 'MinEnergyDraw'),
                'max_energy_draw' => Arr::get($mode, 'MaxEnergyDraw'),

                // Healing beam
                'healing_mode' => Arr::get($mode, 'HealingMode'),
                'healing_per_second' => Arr::get($mode, 'HealingPerSecond'),
                'ammo_per_mscu' => Arr::get($mode, 'AmmoPerMSCU'),
                'medical_ammo_type' => Arr::get($mode, 'MedicalAmmoType'),
                'external_healing' => Arr::get($mode, 'ExternalHealing'),
                'toggle' => Arr::get($mode, 'Toggle'),
                'max_distance' => Arr::get($mode, 'MaxDistance'),
                'max_sensor_distance' => Arr::get($mode, 'MaxSensorDistance'),
                'auto_dosage_modifier' => Arr::get($mode, 'AutoDosageModifier'),
                'healing_break_time' => Arr::get($mode, 'HealingBreakTime'),
                'max_dose_for_auto_adjustment' => Arr::get($mode, 'MaxDoseForAutoAdjustment'),
                'battery_drain_per_second' => Arr::get($mode, 'BatteryDrainPerSecond'),

                // Salvage / Repair
                'material_efficiency' => Arr::get($mode, 'MaterialEfficiency'),
                'max_health_repair_rate' => Arr::get($mode, 'MaxHealthRepairRate'),
                'max_damage_map_repair_rate' => Arr::get($mode, 'MaxDamageMapRepairRate'),
                'health_to_ammo_ratio' => Arr::get($mode, 'HealthToAmmoRatio'),
                'ramp_up_time' => Arr::get($mode, 'RampUpTime'),
                'ramp_down_time' => Arr::get($mode, 'RampDownTime'),
                'max_vehicle_damage_ratio' => Arr::get($mode, 'MaxVehicleDamageRatio'),
                'repaired_material_ratio' => Arr::get($mode, 'RepairedMaterialRatio'),
                'salvage_can_fire_on_full' => Arr::get($mode, 'SalvageCanFireOnFull'),
                'damage_threshold' => Arr::get($mode, 'DamageThreshold'),

                // Collection beam (mining)
                'minimum_distance' => Arr::get($mode, 'MinimumDistance'),
                'maximum_distance' => Arr::get($mode, 'MaximumDistance'),
                'beam_radius' => Arr::get($mode, 'BeamRadius'),
                'collection_rate' => Arr::get($mode, 'CollectionRate'),
                'energy_draw' => Arr::get($mode, 'EnergyDraw'),
                'mining_extractor_tag' => Arr::get($mode, 'MiningExtractorTag'),

                // Tractor beam
                'toggle_mode' => Arr::get($mode, 'ToggleMode'),
            ])
            ->values()
            ->toArray();

        return [
            'class' => Arr::get($weapon, 'WeaponClass'),
            'type' => $this->extractFromStdItem($this->resource, 'DescriptionData.Item Type'),
            'capacity' => Arr::get($ammo, 'Capacity'),
            'range' => Arr::get($weapon, 'EffectiveRange'),

            // deprecated
            'damage_per_shot' => Arr::get($mode, 'Alpha'),
            'regeneration' => Arr::get($weapon, 'Capacitor.MaxRegenPerSec'),

            'rpm' => Arr::get($mode, 'RoundsPerMinute'),

            'damages' => $damages,
            'modes' => $modes,

            'damage' => [
                'sustained_60s' => Arr::get($weapon, 'Damage.Sustained60s'),
                'burst' => Arr::get($weapon, 'Damage.Burst'),
                'alpha_total' => Arr::get($weapon, 'Damage.AlphaTotal'),
                'max' => Arr::get($weapon, 'Damage.Maximum'),
                'maximum' => Arr::get($weapon, 'Damage.Maximum'),  // deprecated: use max
                'dps' => [
                    'physical' => Arr::get($mode, 'DpsPhysical'),
                    'energy' => Arr::get($mode, 'DpsEnergy'),
                    'distortion' => Arr::get($mode, 'DpsDistortion'),
                    'thermal' => Arr::get($mode, 'DpsThermal'),
                    'biochemical' => Arr::get($mode, 'DpsBiochemical'),
                    'stun' => Arr::get($mode, 'DpsStun'),
                ],
                'alpha' => [
                    'physical' => Arr::get($mode, 'AlphaPhysical'),
                    'energy' => Arr::get($mode, 'AlphaEnergy'),
                    'distortion' => Arr::get($mode, 'AlphaDistortion'),
                    'thermal' => Arr::get($mode, 'AlphaThermal'),
                    'biochemical' => Arr::get($mode, 'AlphaBiochemical'),
                    'stun' => Arr::get($mode, 'AlphaStun'),
                ],
            ],

            $this->mergeWhen(Arr::get($mode, 'Spread') !== null, [
                'spread' => [
                    'min' => Arr::get($mode, 'Spread.Minimum'),
                    'max' => Arr::get($mode, 'Spread.Maximum'),
                    'minimum' => Arr::get($mode, 'Spread.Minimum'),  // deprecated: use min
                    'maximum' => Arr::get($mode, 'Spread.Maximum'),  // deprecated: use max
                    'first_attack' => Arr::get($mode, 'Spread.FirstAttack'),
                    'per_attack' => Arr::get($mode, 'Spread.Attack'),
                    'decay' => Arr::get($mode, 'Spread.Decay'),
                ],
            ]),

            $this->mergeWhen(Arr::get($mode, 'BarrelSpinTime') !== null, [
                'barrel_spin_time' => [
                    'up' => Arr::get($mode, 'BarrelSpinTime.Up'),
                    'down' => Arr::get($mode, 'BarrelSpinTime.Down'),
                ],
            ]),

            $this->mergeWhen(! empty($heat), [
                'heat' => [
                    'per_shot' => Arr::get($heat, 'HeatPerShot'),
                    'cooling_delay' => Arr::get($heat, 'CoolingDelay'),
                    'cooling_per_second' => Arr::get($heat, 'CoolingPerSecond'),
                    'overheat_max_shots' => Arr::get($heat, 'ShotsToOverheat'),
                    'overheat_max_time' => Arr::get($heat, 'TimeToOverheat'),
                    'overheat_cooldown' => Arr::get($heat, 'OverheatFixTime'),
                ],
            ]),

            $this->mergeWhen(Arr::get($weapon, 'Capacitor.MaxAmmoLoad') !== null, [
                'capacitor' => [
                    'max_ammo_load' => Arr::get($weapon, 'Capacitor.MaxAmmoLoad'),
                    'regen_per_second' => Arr::get($weapon, 'Capacitor.MaxRegenPerSec'),
                    'cooldown' => Arr::get($weapon, 'Capacitor.Cooldown'),

                    'requested_ammo_load' => Arr::get($weapon, 'Capacitor.RequestedAmmoLoad'),
                    'costs_per_shot' => Arr::get($weapon, 'Capacitor.CostPerBullet'),
                ],
            ]),

            $this->mergeWhen(Arr::get($mode, 'Charge') !== null, [
                'charge' => [
                    'time' => Arr::get($mode, 'Charge.ChargeTime'),
                    'overcharge_time' => Arr::get($mode, 'Charge.OverchargeTime'),
                    'overcharged_time' => Arr::get($mode, 'Charge.OverchargedTime'),
                    'cooldown_time' => Arr::get($mode, 'Charge.CooldownTime'),
                    'auto_fire' => Arr::get($mode, 'Charge.AutoFire'),
                    'require_full_charge' => Arr::get($mode, 'Charge.RequireFullCharge'),
                    'auto_charge' => Arr::get($mode, 'Charge.AutoCharge'),
                    'interpolate_bonus' => Arr::get($mode, 'Charge.InterpolateBonus'),
                ],
                'charge_modifier' => [
                    'damage' => Arr::get($mode, 'ChargeModifier.Damage'),
                    'fire_rate' => Arr::get($mode, 'ChargeModifier.FireRate'),
                    'ammo_speed' => Arr::get($mode, 'ChargeModifier.AmmoSpeed'),
                    'fire_rate_override' => Arr::get($mode, 'ChargeModifier.FireRateOverride'),
                    'pellets_override' => Arr::get($mode, 'ChargeModifier.PelletsOverride'),
                    'burst_shots_override' => Arr::get($mode, 'ChargeModifier.BurstShotsOverride'),
                    'heat_multiplier' => Arr::get($mode, 'ChargeModifier.HeatMultiplier'),
                ],
            ]),

            'ammunition' => new AmmunitionResource($this->resource),
        ];
    }
}
