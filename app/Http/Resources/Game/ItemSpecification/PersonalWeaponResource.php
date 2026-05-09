<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'personal_weapon_mode',
    title: 'Personal Weapon Mode',
    description: 'Fire mode entries as returned by the game data mapping. Type-specific fields are only present when the mode type matches.',
    properties: [
        new OA\Property(property: 'mode', description: 'Mode name (Modes[].Name).', type: 'string', example: 'Rapid', nullable: true),
        new OA\Property(property: 'localised', description: 'Localized label (Modes[].LocalisedName).', type: 'string', example: '[AUTO]', nullable: true),
        new OA\Property(property: 'type', description: 'Fire type (Modes[].FireType).', type: 'string', example: 'rapid', nullable: true),
        new OA\Property(property: 'rpm', description: 'Rounds per minute (Modes[].RoundsPerMinute).', type: 'double', example: 925, nullable: true),
        new OA\Property(property: 'ammo_per_shot', description: 'Ammo consumed per shot (Modes[].AmmoPerShot).', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'pellets_per_shot', description: 'Pellets per shot (Modes[].PelletsPerShot).', type: 'integer', example: 12, nullable: true),
        new OA\Property(property: 'damage_per_second', description: 'Mode DPS as provided (Modes[].DamagePerSecond).', type: 'double', example: 0, nullable: true),

        // Heat / wear (projectile)
        new OA\Property(property: 'heat_per_shot', description: 'Heat generated per shot (projectile modes).', type: 'double', nullable: true),
        new OA\Property(property: 'wear_per_shot', description: 'Durability lost per shot (projectile modes).', type: 'double', nullable: true),

        // Heat / wear (continuous / beam)
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
    schema: 'personal_weapon_damage_vector',
    title: 'Personal Weapon Damage Vector',
    description: 'Damage values broken down by damage type.',
    properties: [
        new OA\Property(property: 'physical', type: 'double', example: 11.5, nullable: true),
        new OA\Property(property: 'energy', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'distortion', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'thermal', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'biochemical', type: 'double', example: 0, nullable: true),
        new OA\Property(property: 'stun', type: 'double', example: 0, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_damage',
    title: 'Personal Weapon Damage',
    description: 'Weapon damage totals and per-type breakdowns from Weapon.Damage.',
    properties: [
        new OA\Property(property: 'dps_total', type: 'double', example: 1150.0, nullable: true),
        new OA\Property(property: 'alpha_total', type: 'double', example: 11.5, nullable: true),
        new OA\Property(property: 'max', description: 'Maximum damage per magazine (Damage.MaxPerMag).', type: 'double', example: 575.0, nullable: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', example: 575.0, nullable: true, deprecated: true),
        new OA\Property(property: 'dps', ref: '#/components/schemas/personal_weapon_damage_vector', nullable: true),
        new OA\Property(property: 'alpha', ref: '#/components/schemas/personal_weapon_damage_vector', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_spread',
    title: 'Personal Weapon Spread',
    description: 'Spread configuration. Only present when spread data exists in the source.',
    properties: [
        new OA\Property(property: 'min', type: 'double', example: 0.1, nullable: true),
        new OA\Property(property: 'max', type: 'double', example: 1.2, nullable: true),
        new OA\Property(property: 'minimum', description: 'Deprecated: Use min.', type: 'double', example: 0.1, nullable: true, deprecated: true),
        new OA\Property(property: 'maximum', description: 'Deprecated: Use max.', type: 'double', example: 1.2, nullable: true, deprecated: true),
        new OA\Property(property: 'first_attack', type: 'double', example: 0.2, nullable: true),
        new OA\Property(property: 'per_attack', type: 'double', example: 0.05, nullable: true),
        new OA\Property(property: 'decay', type: 'double', example: 0.3, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_charge',
    title: 'Personal Weapon Charge',
    description: 'Charge timings. Only present when charge data exists in the source.',
    properties: [
        new OA\Property(property: 'time', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'overcharge_time', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'overcharged_time', type: 'double', example: 0.5, nullable: true),
        new OA\Property(property: 'cooldown_time', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'auto_fire', description: 'Auto-fire when fully charged.', type: 'boolean', nullable: true),
        new OA\Property(property: 'require_full_charge', description: 'Must be fully charged before firing.', type: 'boolean', nullable: true),
        new OA\Property(property: 'auto_charge', description: 'Auto-charges when held.', type: 'boolean', nullable: true),
        new OA\Property(property: 'interpolate_bonus', description: 'Interpolates charge bonus linearly.', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon_charge_modifier',
    title: 'Personal Weapon Charge Modifier',
    description: 'Charge modifiers. Only present when charge data exists in the source.',
    properties: [
        new OA\Property(property: 'damage', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'fire_rate', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'ammo_speed', type: 'double', example: 1.0, nullable: true),
        new OA\Property(property: 'fire_rate_override', description: 'Override fire rate at full charge.', type: 'double', nullable: true),
        new OA\Property(property: 'pellets_override', description: 'Override pellet count at full charge.', type: 'integer', nullable: true),
        new OA\Property(property: 'burst_shots_override', description: 'Override burst shot count at full charge.', type: 'integer', nullable: true),
        new OA\Property(property: 'heat_multiplier', description: 'Heat generation multiplier at full charge.', type: 'double', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'personal_weapon',
    title: 'Personal Weapon',
    description: 'FPS weapon specification sourced from Item.stdItem.Weapon and Item.stdItem.Ammunition for WeaponPersonal items. Legacy v2 fields are preserved and marked deprecated.',
    properties: [
        new OA\Property(property: 'class', description: 'Class as provided by DescriptionData.Class.', type: 'string', example: 'Medium', nullable: true),
        new OA\Property(property: 'type', description: 'Type as provided by DescriptionData.Item Type.', type: 'string', example: 'Rifle', nullable: true),

        new OA\Property(
            property: 'magazine_type',
            description: 'Deprecated: legacy field, currently returned as an empty string; do not use.',
            type: 'string',
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'magazine_size',
            description: 'Deprecated: use `capacity`.',
            type: 'integer',
            example: 50,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(
            property: 'effective_range',
            description: 'Deprecated: use `range`.',
            type: 'double',
            example: 950,
            nullable: true,
            deprecated: true
        ),

        new OA\Property(property: 'capacity', description: 'Weapon capacity (Weapon.Capacity).', type: 'integer', example: 50, nullable: true),
        new OA\Property(property: 'range', description: 'Effective range in meters (Weapon.EffectiveRange).', type: 'double', example: 950, nullable: true),

        new OA\Property(
            property: 'damage_per_shot',
            description: 'Deprecated: use `damage.alpha_total` (or per-type `damage.alpha.*`) instead.',
            type: 'double',
            example: 11.5,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(property: 'pellets_per_shot', description: 'Pellets per shot (Weapon.PelletsPerShot).', type: 'integer', example: 12, nullable: true),

        new OA\Property(
            property: 'rof',
            description: 'Deprecated: use `rpm`.',
            type: 'double',
            example: 925,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(property: 'rpm', description: 'Rounds per minute for the first mode (Modes[0].RoundsPerMinute).', type: 'double', example: 925, nullable: true),

        new OA\Property(
            property: 'damages',
            description: 'Deprecated: legacy ammunition-derived entries. Prefer `damage` for weapon damage totals/breakdowns.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_entry'),
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'modes',
            description: 'Fire modes returned from Weapon.Modes.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/personal_weapon_mode'),
            nullable: true
        ),

        new OA\Property(property: 'fire_mode', description: 'Weapon fire mode (Weapon.FireMode).', type: 'string', example: 'Auto', nullable: true),

        new OA\Property(property: 'damage', ref: '#/components/schemas/personal_weapon_damage'),

        new OA\Property(property: 'spread', ref: '#/components/schemas/personal_weapon_spread', nullable: true),
        new OA\Property(property: 'ads_spread', ref: '#/components/schemas/personal_weapon_spread', nullable: true),

        new OA\Property(property: 'charge', ref: '#/components/schemas/personal_weapon_charge', nullable: true),
        new OA\Property(property: 'charge_modifier', ref: '#/components/schemas/personal_weapon_charge_modifier', nullable: true),

        new OA\Property(
            property: 'ammunition',
            description: 'Deprecated: use the root-level ammunition resource (outside this specification payload) where available.',
            type: 'object',
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'weapon_damage_entry',
    title: 'Weapon Damage Entry',
    description: 'Legacy ammo-derived damage entry.',
    properties: [
        new OA\Property(property: 'type', description: 'Damage context.', type: 'string', example: 'impact', nullable: true),
        new OA\Property(property: 'name', description: 'Damage type name.', type: 'string', example: 'physical', nullable: true),
        new OA\Property(property: 'damage', type: 'double', example: 11.5, nullable: true),
    ],
    type: 'object'
)]
class PersonalWeaponResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $ammo = $this->extractFromStdItem($this->resource, 'Ammunition');
        $weapon = $this->extractFromStdItem($this->resource, 'Weapon');
        $mode = Arr::get($weapon, 'Modes.0');
        $damage = Arr::get($weapon, 'Damage');

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
            'class' => $this->extractFromStdItem($this->resource, 'DescriptionData.Class'),
            'type' => $this->extractFromStdItem($this->resource, 'DescriptionData.Item Type'),

            // deprecated
            'magazine_type' => '',
            // deprecated
            'magazine_size' => Arr::get($weapon, 'Capacity'),

            // deprecated
            'effective_range' => Arr::get($weapon, 'EffectiveRange'),

            'capacity' => Arr::get($weapon, 'Capacity'),

            'range' => Arr::get($weapon, 'EffectiveRange'),

            'damage_per_shot' => Arr::get($mode, 'Alpha'),
            'pellets_per_shot' => Arr::get($weapon, 'PelletsPerShot'),

            // deprecated
            'rof' => Arr::get($mode, 'RoundsPerMinute'),

            'rpm' => Arr::get($mode, 'RoundsPerMinute'),

            'damages' => $damages,
            'modes' => $modes,

            'fire_mode' => Arr::get($weapon, 'FireMode'),

            'damage' => [
                'dps_total' => Arr::get($damage, 'DpsTotal'),
                'alpha_total' => Arr::get($damage, 'AlphaTotal'),
                'max' => Arr::get($damage, 'MaxPerMag'),
                'maximum' => Arr::get($damage, 'MaxPerMag'),  // deprecated: use max
                'dps' => [
                    'physical' => Arr::get($weapon, 'Damage.Dps.Physical'),
                    'energy' => Arr::get($weapon, 'Damage.Dps.Energy'),
                    'distortion' => Arr::get($weapon, 'Damage.Dps.Distortion'),
                    'thermal' => Arr::get($weapon, 'Damage.Dps.Thermal'),
                    'biochemical' => Arr::get($weapon, 'Damage.Dps.Biochemical'),
                    'stun' => Arr::get($weapon, 'Damage.Dps.Stun'),
                ],
                'alpha' => [
                    'physical' => Arr::get($weapon, 'Damage.Alpha.Physical'),
                    'energy' => Arr::get($weapon, 'Damage.Alpha.Energy'),
                    'distortion' => Arr::get($weapon, 'Damage.Alpha.Distortion'),
                    'thermal' => Arr::get($weapon, 'Damage.Alpha.Thermal'),
                    'biochemical' => Arr::get($weapon, 'Damage.Alpha.Biochemical'),
                    'stun' => Arr::get($weapon, 'Damage.Alpha.Stun'),
                ],
            ],

            $this->mergeWhen(Arr::get($mode, 'Spread.Minimum') !== null, [
                'spread' => [
                    'min' => Arr::get($weapon, 'Spread.Minimum'),
                    'max' => Arr::get($weapon, 'Spread.Maximum'),
                    'minimum' => Arr::get($weapon, 'Spread.Minimum'),  // deprecated: use min
                    'maximum' => Arr::get($weapon, 'Spread.Maximum'),  // deprecated: use max
                    'first_attack' => Arr::get($weapon, 'Spread.FirstAttack'),
                    'per_attack' => Arr::get($weapon, 'Spread.Attack'),
                    'decay' => Arr::get($weapon, 'Spread.Decay'),
                ],
                'ads_spread' => [
                    'min' => Arr::get($weapon, 'AdsSpread.Min') == 0 ? null : Arr::get($weapon, 'AdsSpread.Min'),
                    'max' => Arr::get($weapon, 'AdsSpread.Max') == 0 ? null : Arr::get($weapon, 'AdsSpread.Max'),
                    'minimum' => Arr::get($weapon, 'AdsSpread.Min') == 0 ? null : Arr::get($weapon, 'AdsSpread.Min'),  // deprecated: use min
                    'maximum' => Arr::get($weapon, 'AdsSpread.Max') == 0 ? null : Arr::get($weapon, 'AdsSpread.Max'),  // deprecated: use max
                    'first_attack' => Arr::get($weapon, 'AdsSpread.FirstAttack') == 0 ? null : Arr::get($weapon, 'AdsSpread.FirstAttack'),
                    'per_attack' => Arr::get($weapon, 'AdsSpread.Attack') == 0 ? null : Arr::get($weapon, 'AdsSpread.Attack'),
                    'decay' => Arr::get($weapon, 'AdsSpread.Decay') == 0 ? null : Arr::get($weapon, 'AdsSpread.Decay'),
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
