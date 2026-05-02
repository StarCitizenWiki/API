<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ExpandsUexPrices;
use App\Http\Resources\Game\Concerns\ExtractsJsonData;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Item\ItemInventoryResource;
use App\Http\Resources\Game\ItemSpecification\ArmorResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\StarCitizen\Vehicle\ComponentResource;
use App\Http\Resources\StarCitizen\Vehicle\VehicleLoanerResource;
use App\Http\Resources\StarCitizen\Vehicle\VehicleSkuResource;
use App\Models\Game\ItemData;
use App\Models\Game\VehicleData;
use App\Services\Game\WeaponSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_vehicle',
    title: 'Game Vehicle',
    description: 'Vehicle data imported from game files for a specific game version.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique vehicle identifier.', type: 'string', example: 'b5892cde-e805-4626-94a2-89fdf6eedc1c'),
        new OA\Property(property: 'name', description: 'Display name of the vehicle.', type: 'string', example: 'Avenger Titan'),
        new OA\Property(property: 'game_name', description: 'Internal game class name.', type: 'string', example: 'AEGS_Avenger_Titan', nullable: true),
        new OA\Property(property: 'slug', description: 'URL-friendly vehicle identifier.', type: 'string', example: 'avenger-titan'),
        new OA\Property(property: 'class_name', description: 'class name.', type: 'string', example: 'AEGS_Avenger_Titan'),
        new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
        new OA\Property(property: 'size_class', description: 'Vehicle size classification (1–6).', type: 'integer', example: 2, nullable: true),
        new OA\Property(
            property: 'dimension',
            description: 'Vehicle physical dimensions in meters.',
            properties: [
                new OA\Property(property: 'length', description: 'Length in meters.', type: 'number', example: 18.0),
                new OA\Property(property: 'width', description: 'Width (beam) in meters.', type: 'number', example: 16.0),
                new OA\Property(property: 'height', description: 'Height in meters.', type: 'number', example: 5.0),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'sizes',
            description: 'Deprecated, use dimension instead.',
            properties: [
                new OA\Property(property: 'length', type: 'number', example: 18.0),
                new OA\Property(property: 'beam', type: 'number', example: 16.0),
                new OA\Property(property: 'height', type: 'number', example: 5.0),
            ],
            type: 'object',
            deprecated: true
        ),
        new OA\Property(property: 'emission', description: 'Deprecated, use signature instead. Emission.ir currently maps to IR with shields active. Emission.em_max maps to EM Signature with quantum drive active. Emission.em_idle maps to EM Signature with shields active.', properties: [
            new OA\Property(property: 'ir', type: 'number', example: 4412, nullable: true),
            new OA\Property(property: 'em_idle', type: 'number', example: 14320, nullable: true),
            new OA\Property(property: 'em_max', type: 'number', example: 30458, nullable: true),
        ], type: 'object', nullable: true, deprecated: true),
        new OA\Property(property: 'mass', description: 'Deprecated, use mass_total instead. Mass is equal to mass_hull.', type: 'number', example: 53531.0, nullable: true),
        new OA\Property(property: 'mass_hull', description: 'Hull mass without loadout in kg.', type: 'number', example: 53531.0, nullable: true),
        new OA\Property(property: 'mass_loadout', description: 'Equipped loadout mass in kg.', type: 'number', example: 0, nullable: true),
        new OA\Property(property: 'mass_total', description: 'Total mass (hull + loadout) in kg.', type: 'number', example: 53531.0, nullable: true),
        new OA\Property(property: 'cargo_capacity', description: 'Cargo capacity in SCU.', type: 'number', example: 8, nullable: true),
        new OA\Property(
            property: 'cargo_grids',
            description: 'Cargo grid containers from ship data.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/item_inventory'),
            nullable: true
        ),
        new OA\Property(property: 'cargo_limits', description: 'Calculated cargo size limits based on grid dimensions.', properties: [
            new OA\Property(property: 'min', properties: [
                new OA\Property(property: 'x', type: 'number', example: 1.25, nullable: true),
                new OA\Property(property: 'y', type: 'number', example: 1.25, nullable: true),
                new OA\Property(property: 'z', type: 'number', example: 1.25, nullable: true),
            ], type: 'object', nullable: true),
            new OA\Property(property: 'max', properties: [
                new OA\Property(property: 'x', type: 'number', example: 2.5, nullable: true),
                new OA\Property(property: 'y', type: 'number', example: 2.5, nullable: true),
                new OA\Property(property: 'z', type: 'number', example: 1.25, nullable: true),
            ], type: 'object', nullable: true),
        ], type: 'object', nullable: true),
        new OA\Property(property: 'vehicle_inventory', description: 'Vehicle stowage in micro SCU', type: 'number', example: 0, nullable: true),
        new OA\Property(property: 'inventory_containers', description: 'Personal inventory containers (stowage) from ship data.', type: 'array', items: new OA\Items(ref: '#/components/schemas/item_inventory'), nullable: true),
        new OA\Property(
            property: 'crew',
            description: 'Crew requirements.',
            properties: [
                new OA\Property(property: 'min', description: 'Minimum crew required to operate.', type: 'integer', example: 1, nullable: true),
                new OA\Property(property: 'max', description: 'Maximum crew capacity.', type: 'integer', example: 1, nullable: true),
                new OA\Property(property: 'weapon', description: 'Number of weapon crew stations.', type: 'integer', example: 1, nullable: true),
                new OA\Property(property: 'operation', description: 'Number of operational crew stations.', type: 'integer', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'max_medical_tier',
            description: 'Highest medical bed tier available (e.g., "T2", "T3"). Null if no medical beds.',
            type: 'string',
            example: 'T3',
            nullable: true,
        ),
        new OA\Property(
            property: 'seating',
            description: 'Seating and bed summary.',
            properties: [
                new OA\Property(property: 'crew_stations', description: 'Total number of crew stations.', type: 'integer', example: 14),
                new OA\Property(property: 'ejection_seats', description: 'Number of ejection seats.', type: 'integer', example: 1),
                new OA\Property(property: 'escape_pods', description: 'Number of escape pods. Null when absent.', type: 'integer', example: 4, nullable: true),
                new OA\Property(property: 'jump_seats', description: 'Number of jump seats. Null when absent.', type: 'integer', example: 4, nullable: true),
                new OA\Property(property: 'beds', description: 'Total number of beds.', type: 'integer', example: 22),
                new OA\Property(
                    property: 'medical_beds',
                    description: 'Medical bed counts by tier. Null if no medical beds.',
                    type: 'object',
                    example: '{"T2": 1, "T3": 4}',
                    nullable: true,
                ),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'is_vehicle', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_gravlev', type: 'boolean', example: false, nullable: true),
        new OA\Property(property: 'is_spaceship', type: 'boolean', example: true, nullable: true),
        new OA\Property(property: 'health', description: 'Total vehicle health pool.', type: 'number', example: 2500, nullable: true),
        new OA\Property(property: 'shield_hp', description: 'Use shield.hp property instead.', type: 'number', example: 12000, nullable: true, deprecated: true),
        new OA\Property(property: 'shield_face_type', description: 'Use shield.face_type property instead.', type: 'string', example: 'FourFaces', nullable: true, deprecated: true),
        new OA\Property(
            property: 'shield',
            description: 'Shield system data from scunpacked.',
            properties: [
                new OA\Property(property: 'hp', description: 'Total shield hit points.', type: 'number', example: 12000, nullable: true),
                new OA\Property(property: 'regeneration', description: 'Shield regeneration rate per second.', type: 'number', example: 50, nullable: true),
                new OA\Property(property: 'face_type', description: 'Shield face configuration (e.g., Bubble, Quadrant).', type: 'string', example: 'FourFaces', nullable: true),
                new OA\Property(property: 'max_reallocation', description: 'Maximum shield reallocation ratio (0–1).', type: 'number', example: 0.5, nullable: true),
                new OA\Property(property: 'reconfiguration_cooldown', description: 'Cooldown time for shield reconfiguration in seconds.', type: 'number', example: 2.0, nullable: true),
                new OA\Property(property: 'max_electrical_charge_damage_rate', description: 'Maximum electrical charge damage rate.', type: 'number', example: 100, nullable: true),
                new OA\Property(property: 'resistance', description: 'Shield resistance values by damage type.', properties: [
                    new OA\Property(property: 'physical', description: 'Physical shield resistance.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'energy', description: 'Energy shield resistance.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'distortion', description: 'Distortion shield resistance.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'thermal', description: 'Thermal shield resistance.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'biochemical', description: 'Biochemical shield resistance.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'stun', description: 'Stun shield resistance.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                ], type: 'object', nullable: true),
                new OA\Property(property: 'absorption', description: 'Shield absorption values by damage type.', properties: [
                    new OA\Property(property: 'physical', description: 'Physical shield absorption.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'energy', description: 'Energy shield absorption.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'distortion', description: 'Distortion shield absorption.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'thermal', description: 'Thermal shield absorption.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'biochemical', description: 'Biochemical shield absorption.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                    new OA\Property(property: 'stun', description: 'Stun shield absorption.', properties: [
                        new OA\Property(property: 'minimum', type: 'number', nullable: true),
                        new OA\Property(property: 'maximum', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                ], type: 'object', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'weapon_snapshot',
            description: 'Computed weapon statistics from vehicle loadout',
            properties: [
                new OA\Property(property: 'pilot_guns_count', type: 'integer', example: 3),
                new OA\Property(property: 'turrets_manned_count', type: 'integer', example: 0),
                new OA\Property(property: 'turrets_remote_count', type: 'integer', example: 0),
                new OA\Property(property: 'turret_weapon_guns_count', type: 'integer', example: 0),
                new OA\Property(property: 'missile_rack_count', type: 'integer', example: 2),
                new OA\Property(property: 'missile_count', type: 'integer', example: 2),
                new OA\Property(property: 'countermeasures_count', type: 'integer', example: 2),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'speed',
            description: 'Flight speed characteristics in m/s.',
            properties: [
                new OA\Property(property: 'scm', description: 'Space Combat Maneuvering speed in m/s.', type: 'number', example: 260),
                new OA\Property(property: 'max', description: 'Maximum speed in m/s.', type: 'number', example: 1425),
                new OA\Property(property: 'boost_forward', description: 'Forward boost speed in m/s.', type: 'number', example: 610, nullable: true),
                new OA\Property(property: 'boost_backward', description: 'Backward boost speed in m/s.', type: 'number', example: 280, nullable: true),
                new OA\Property(property: 'zero_to_scm', description: 'Time from zero to SCM speed in seconds.', type: 'number', example: 2.04, nullable: true),
                new OA\Property(property: 'zero_to_max', description: 'Time from zero to max speed in seconds.', type: 'number', example: 11.19, nullable: true),
                new OA\Property(property: 'scm_to_zero', description: 'Time from SCM to zero in seconds.', type: 'number', example: 6368.27, nullable: true),
                new OA\Property(property: 'max_to_zero', description: 'Time from max to zero in seconds.', type: 'number', example: 34903.01, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'agility',
            description: 'Angular rates and acceleration characteristics.',
            properties: [
                new OA\Property(property: 'pitch', description: 'Pitch angular rate in deg/s.', type: 'number', example: 52.5, nullable: true),
                new OA\Property(property: 'yaw', description: 'Yaw angular rate in deg/s.', type: 'number', example: 48.5, nullable: true),
                new OA\Property(property: 'roll', description: 'Roll angular rate in deg/s.', type: 'number', example: 180, nullable: true),
                new OA\Property(property: 'pitch_boosted', description: 'Pitch angular rate with boost in deg/s.', type: 'number', example: 63, nullable: true),
                new OA\Property(property: 'yaw_boosted', description: 'Yaw angular rate with boost in deg/s.', type: 'number', example: 58.2, nullable: true),
                new OA\Property(property: 'roll_boosted', description: 'Roll angular rate with boost in deg/s.', type: 'number', example: 216, nullable: true),
                new OA\Property(property: 'acceleration', description: 'Linear acceleration values.', properties: [
                    new OA\Property(property: 'main', description: 'Forward acceleration in m/s².', type: 'number', example: 127.339, nullable: true),
                    new OA\Property(property: 'retro', description: 'Retro (backward) acceleration in m/s².', type: 'number', example: 0.041, nullable: true),
                    new OA\Property(property: 'vtol', description: 'VTOL acceleration in m/s².', type: 'number', example: 0, nullable: true),
                    new OA\Property(property: 'maneuvering', description: 'Maneuvering thruster acceleration in m/s².', type: 'number', example: 121.49, nullable: true),
                    new OA\Property(property: 'main_g', description: 'Forward acceleration in G.', type: 'number', example: 12.985, nullable: true),
                    new OA\Property(property: 'retro_g', description: 'Retro acceleration in G.', type: 'number', example: 0.004, nullable: true),
                    new OA\Property(property: 'vtol_g', description: 'VTOL acceleration in G.', type: 'number', example: 0, nullable: true),
                    new OA\Property(property: 'maneuvering_g', description: 'Maneuvering acceleration in G.', type: 'number', example: 12.389, nullable: true),
                ], type: 'object'),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'afterburner',
            description: 'Afterburner properties from FlightCharacteristics.Afterburner.',
            properties: [
                new OA\Property(property: 'pitch_boost_multiplier', description: 'Pitch angular acceleration multiplier when boosting.', type: 'number', example: 1.2, nullable: true),
                new OA\Property(property: 'roll_boost_multiplier', description: 'Roll angular acceleration multiplier when boosting.', type: 'number', example: 1.2, nullable: true),
                new OA\Property(property: 'yaw_boost_multiplier', description: 'Yaw angular acceleration multiplier when boosting.', type: 'number', example: 1.2, nullable: true),
                new OA\Property(property: 'capacitor', description: 'Maximum afterburner capacitor capacity.', type: 'number', example: 20, nullable: true),
                new OA\Property(property: 'idle_cost', description: 'Afterburner capacitor idle drain per second.', type: 'number', example: 1, nullable: true),
                new OA\Property(property: 'linear_cost', description: 'Afterburner capacitor cost for linear thrust.', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'angular_cost', description: 'Afterburner capacitor cost for angular thrust.', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'regen_per_second', description: 'Capacitor regeneration per second.', type: 'number', example: 0.75, nullable: true),
                new OA\Property(property: 'regen_delay_after_use', description: 'Delay before regeneration starts after use in seconds.', type: 'number', example: 0.2, nullable: true),
                new OA\Property(property: 'pre_delay_time', description: 'Pre-delay time before afterburner engages in seconds.', type: 'number', example: 0, nullable: true),
                new OA\Property(property: 'ramp_up_time', description: 'Ramp-up time to full afterburner in seconds.', type: 'number', example: 0.4, nullable: true),
                new OA\Property(property: 'ramp_down_time', description: 'Ramp-down time from afterburner in seconds.', type: 'number', example: 0.2, nullable: true),
                new OA\Property(property: 'regen_time', description: 'Total time to fully regenerate capacitor in seconds.', type: 'number', example: 26.67, nullable: true),
                new OA\Property(property: 'regen_delay', description: 'Delay before capacitor regeneration in seconds.', type: 'number', example: 0.2, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'fuel',
            description: 'Hydrogen fuel capacity, intake rate, and usage.',
            properties: [
                new OA\Property(property: 'capacity', description: 'Total hydrogen fuel capacity.', type: 'number', example: 9),
                new OA\Property(property: 'intake_rate', description: 'Fuel intake rate from scoops.', type: 'number', example: 22, nullable: true),
                new OA\Property(
                    property: 'usage',
                    description: 'Fuel consumption rates by thruster type.',
                    properties: [
                        new OA\Property(property: 'main', description: 'Main engine fuel consumption rate.', type: 'number', example: 40.1177, nullable: true),
                        new OA\Property(property: 'retro', description: 'Retro thruster fuel consumption rate.', type: 'number', example: 0.0128, nullable: true),
                        new OA\Property(property: 'vtol', description: 'VTOL fuel consumption rate.', type: 'number', example: 0, nullable: true),
                        new OA\Property(property: 'maneuvering', description: 'Maneuvering thruster fuel consumption rate.', type: 'number', example: 18.5981, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'quantum',
            description: 'Quantum travel system parameters.',
            properties: [
                new OA\Property(property: 'quantum_speed', description: 'Quantum travel speed in m/s.', type: 'number', example: 165000000, nullable: true),
                new OA\Property(property: 'quantum_spool_time', description: 'Quantum drive spool-up time in seconds.', type: 'number', example: 4, nullable: true),
                new OA\Property(property: 'quantum_fuel_capacity', description: 'Quantum fuel capacity.', type: 'number', example: 1.1, nullable: true),
                new OA\Property(property: 'quantum_range', description: 'Maximum quantum travel range in meters.', type: 'number', example: 112244897.9592, nullable: true),
                new OA\Property(property: 'port_olisar_to_arccorp_time', description: 'Reference travel time from Port Olisar to ArcCorp in seconds.', type: 'number', example: 254.105158, nullable: true),
                new OA\Property(property: 'port_olisar_to_arccorp_fuel', description: 'Reference fuel usage from Port Olisar to ArcCorp.', type: 'number', example: 410.888040486, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'armor',
            description: 'Vehicle armor data from ArmorResource. Deprecated plural key aliases (signal_multipliers, damage_multipliers, resistance_multipliers) are emitted for backward compatibility.',
            ref: '#/components/schemas/vehicle_armor',
            nullable: true
        ),
        new OA\Property(
            property: 'propulsion',
            description: 'Vehicle propulsion thruster data.',
            properties: [
                new OA\Property(
                    property: 'thrusters',
                    description: 'Array of thruster groups by type.',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'type', description: 'Thruster type (Main, Maneuver, Retro, etc).', type: 'string', example: 'Main'),
                            new OA\Property(property: 'count', description: 'Number of thrusters of this type.', type: 'integer', example: 1),
                            new OA\Property(property: 'capacity', description: 'Thruster capacity.', type: 'number', example: 8.65, nullable: true),
                            new OA\Property(property: 'g', description: 'G-force rating.', type: 'number', example: 14.19, nullable: true),
                        ],
                        type: 'object'
                    ),
                ),
                new OA\Property(
                    property: 'thrust_capacity',
                    description: 'Directional thrust capacity values.',
                    properties: [
                        new OA\Property(property: 'main', description: 'Main engine thrust in Newtons.', type: 'number', example: 3566000, nullable: true),
                        new OA\Property(property: 'retro', description: 'Retro thruster thrust in Newtons.', type: 'number', example: 890000, nullable: true),
                        new OA\Property(property: 'vtol', description: 'VTOL thruster thrust in Newtons.', type: 'number', example: 0, nullable: true),
                        new OA\Property(property: 'maneuvering', description: 'Maneuvering thruster thrust in Newtons.', type: 'number', example: 1200000, nullable: true),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'weaponry',
            description: 'Computed weapon DPS and damage statistics from scunpacked.',
            properties: [
                new OA\Property(property: 'pilot_dps', description: 'Total pilot weapon DPS.', type: 'number', example: 1200.5, nullable: true),
                new OA\Property(property: 'pilot_alpha', description: 'Total pilot weapon alpha damage.', type: 'number', example: 350.0, nullable: true),
                new OA\Property(property: 'pilot_sustained_dps', description: 'Total pilot weapon sustained DPS.', type: 'number', example: 980.0, nullable: true),
                new OA\Property(property: 'turret_dps', description: 'Total turret weapon DPS.', type: 'number', nullable: true),
                new OA\Property(property: 'turret_alpha', description: 'Total turret weapon alpha damage.', type: 'number', nullable: true),
                new OA\Property(property: 'turret_sustained_dps', description: 'Total turret weapon sustained DPS.', type: 'number', nullable: true),
                new OA\Property(property: 'fixed_weapons', description: 'Fixed weapon aggregate stats.', properties: [
                    new OA\Property(property: 'dps_total', type: 'number', nullable: true),
                    new OA\Property(property: 'sustained_dps_total', type: 'number', nullable: true),
                    new OA\Property(property: 'alpha_total', type: 'number', nullable: true),
                    new OA\Property(property: 'weapons', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'name', type: 'string', nullable: true),
                        new OA\Property(property: 'dps', type: 'number', nullable: true),
                        new OA\Property(property: 'sustained_dps', type: 'number', nullable: true),
                        new OA\Property(property: 'alpha', type: 'number', nullable: true),
                    ], type: 'object'), nullable: true),
                ], type: 'object', nullable: true),
                new OA\Property(property: 'missiles', description: 'Missile statistics.', properties: [
                    new OA\Property(property: 'count', type: 'integer', example: 4, nullable: true),
                    new OA\Property(property: 'damage', properties: [
                        new OA\Property(property: 'physical', type: 'number', nullable: true),
                        new OA\Property(property: 'energy', type: 'number', nullable: true),
                        new OA\Property(property: 'distortion', type: 'number', nullable: true),
                        new OA\Property(property: 'thermal', type: 'number', nullable: true),
                        new OA\Property(property: 'biochemical', type: 'number', nullable: true),
                        new OA\Property(property: 'stun', type: 'number', nullable: true),
                        new OA\Property(property: 'total', type: 'number', nullable: true),
                    ], type: 'object', nullable: true),
                ], type: 'object', nullable: true),
                new OA\Property(property: 'total_missile_damage', description: 'Total missile damage.', type: 'integer', example: 8, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),

        new OA\Property(
            property: 'insurance',
            description: 'Insurance claim times and expedite costs.',
            properties: [
                new OA\Property(property: 'claim_time', description: 'Standard claim time in minutes.', type: 'number', example: 4.05, nullable: true),
                new OA\Property(property: 'expedite_time', description: 'Expedited claim time in minutes.', type: 'number', example: 1.35, nullable: true),
                new OA\Property(property: 'expedite_cost', description: 'Cost to expedite the claim in aUEC.', type: 'number', example: 2343, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'damage_limits',
            properties: [
                new OA\Property(
                    property: 'before_destruction',
                    description: 'Map of part name => damage capacity before destruction.',
                    type: 'object',
                    example: ['Nose' => 2500, 'Body' => 2500],
                    nullable: true
                ),
                new OA\Property(
                    property: 'before_detach',
                    description: 'Map of part name => damage before detachment.',
                    type: 'object',
                    example: ['WingTipLeft' => 1000, 'WingTipRight' => 1000],
                    nullable: true
                ),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'ports',
            description: 'Equipment ports from ship loadout. Only included on show route, excluded from index route.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_port'),
            nullable: true
        ),
        new OA\Property(
            property: 'hardpoints',
            description: 'Legacy v2 equipment hardpoints from ship loadout. Only included on show route via legacy v2 API, excluded from index route. Use `ports` in current API.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_hardpoint'),
            nullable: true
        ),
        new OA\Property(
            property: 'parts',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_vehicle_part'),
            nullable: true
        ),
        new OA\Property(
            property: 'turrets',
            properties: [
                new OA\Property(property: 'manned', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle_turret'), nullable: true),
                new OA\Property(property: 'remote', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle_turret'), nullable: true),
                new OA\Property(property: 'pdc', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_vehicle_turret'), nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'cross_section',
            description: 'Vehicle cross-section dimensions from scunpacked.',
            properties: [
                new OA\Property(property: 'length', description: 'Cross-section length (X axis).', type: 'number', example: 18.0, nullable: true),
                new OA\Property(property: 'width', description: 'Cross-section width (Y axis).', type: 'number', example: 16.0, nullable: true),
                new OA\Property(property: 'height', description: 'Cross-section height (Z axis).', type: 'number', example: 5.0, nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'cross_section_max', description: 'Derived: maximum cross-section dimension (max of x, y, z)', type: 'number', example: 18.0, nullable: true),
        new OA\Property(
            property: 'signature',
            description: 'EM and IR signature data from scunpacked.',
            properties: [
                new OA\Property(property: 'ir_quantum', description: 'Infrared signature with quantum drive active.', type: 'number', example: 4412, nullable: true),
                new OA\Property(property: 'ir_shields', description: 'Infrared signature with shields active.', type: 'number', example: 14320, nullable: true),
                new OA\Property(property: 'em_quantum', description: 'Electromagnetic signature with quantum drive active.', type: 'number', example: 30458, nullable: true),
                new OA\Property(property: 'em_shields', description: 'Electromagnetic signature with shields active.', type: 'number', example: 14320, nullable: true),
                new OA\Property(property: 'em_groups_quantum', description: 'EM signature groups with quantum drive active.', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'em_groups_shields', description: 'EM signature groups with shields active.', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'em_segment_groups_quantum', description: 'EM segment groups with quantum drive active.', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'em_segment_groups_shields', description: 'EM segment groups with shields active.', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'em_per_segment', description: 'EM signature per segment.', type: 'number', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'cooling',
            description: 'Cooling system segment allocation.',
            properties: [
                new OA\Property(property: 'generation_segments', description: 'Total cooling generation segments available.', type: 'number', nullable: true),
                new OA\Property(property: 'usage_shields_pct', description: 'Percentage of cooling used by shields.', type: 'number', nullable: true),
                new OA\Property(property: 'usage_quantum_pct', description: 'Percentage of cooling used by quantum drive.', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_shields', description: 'Cooling segments consumed by shields.', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_quantum', description: 'Cooling segments consumed by quantum drive.', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_shields_grouped', description: 'Grouped cooling segments for shields.', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
                new OA\Property(property: 'used_segments_quantum_grouped', description: 'Grouped cooling segments for quantum drive.', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'power',
            description: 'Power system segment allocation.',
            properties: [
                new OA\Property(property: 'generation_segments', description: 'Total power generation segments available.', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_shields', description: 'Power segments consumed by shields.', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_quantum', description: 'Power segments consumed by quantum drive.', type: 'number', nullable: true),
                new OA\Property(property: 'used_segments_grouped', description: 'Grouped power segments consumed.', type: 'array', items: new OA\Items(type: 'number'), nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(
            property: 'power_pools',
            description: 'Power pool allocation limits by component type. Size of -1 indicates unlimited pool.',
            type: 'object',
            example: [
                'WeaponGun' => ['type' => 'FixedPowerPool', 'item_type' => 'WeaponGun', 'size' => 4],
                'Shield' => ['type' => 'DynamicPowerPool', 'item_type' => 'Shield', 'size' => 2],
                'FlightController' => ['type' => 'DynamicPowerPool', 'item_type' => 'FlightController', 'size' => -1],
            ],
            nullable: true,
            additionalProperties: new OA\AdditionalProperties(
                properties: [
                    new OA\Property(property: 'type', type: 'string', example: 'FixedPowerPool'),
                    new OA\Property(property: 'item_type', type: 'string', example: 'WeaponGun'),
                    new OA\Property(property: 'size', description: 'Power pool size. -1 indicates unlimited.', type: 'integer', example: 4),
                ],
                type: 'object',
                nullable: true
            )
        ),
        new OA\Property(
            property: 'penetration_multiplier',
            description: 'Penetration multiplier values for fuse and components.',
            properties: [
                new OA\Property(property: 'fuse', description: 'Fuse penetration multiplier.', type: 'number', nullable: true),
                new OA\Property(property: 'components', description: 'Components penetration multiplier.', type: 'number', nullable: true),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'career', description: 'Primary career classification (see GET /api/vehicles/filters for valid values).', type: 'string', example: 'Light Freight', nullable: true),
        new OA\Property(property: 'role', description: 'Specific role within the career (see GET /api/vehicles/filters for valid values).', type: 'string', example: 'Combat', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', example: 'https://example.com/vehicles/uuid', nullable: true),
        new OA\Property(property: 'link', type: 'string', example: 'https://api.example.com/vehicles/uuid'),
        new OA\Property(property: 'description', ref: '#/components/schemas/translation', description: 'Ship-Matrix vehicle description', nullable: true),
        new OA\Property(
            property: 'id',
            description: 'Ship-Matrix CIG ID',
            type: 'integer',
            nullable: true
        ),
        new OA\Property(
            property: 'chassis_id',
            description: 'Ship-Matrix chassis ID',
            type: 'integer',
            nullable: true
        ),
        new OA\Property(
            property: 'shipmatrix_name',
            description: 'Ship-Matrix vehicle name',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'foci',
            description: 'Ship-Matrix vehicle foci/roles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/translation'),
            nullable: true
        ),
        new OA\Property(
            property: 'production_status',
            ref: '#/components/schemas/translation',
            description: 'Ship-Matrix production status',
            nullable: true
        ),
        new OA\Property(
            property: 'production_note',
            ref: '#/components/schemas/translation',
            description: 'Ship-Matrix production note',
            nullable: true
        ),
        new OA\Property(
            property: 'type',
            ref: '#/components/schemas/translation',
            description: 'Ship-Matrix vehicle type',
            nullable: true
        ),
        new OA\Property(
            property: 'msrp',
            description: 'Ship-Matrix MSRP in USD',
            type: 'number',
            nullable: true
        ),
        new OA\Property(
            property: 'pledge_url',
            description: 'Link to RSI pledge store',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'loaner',
            description: 'Ship-Matrix loaner vehicles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_loaner'),
            nullable: true
        ),
        new OA\Property(
            property: 'skus',
            description: 'Ship-Matrix SKUs',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_sku'),
            nullable: true
        ),
        new OA\Property(
            property: 'components',
            description: 'Ship-Matrix components (only included when ?include=components)',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/vehicle_component'),
            nullable: true
        ),
        new OA\Property(
            property: 'uex_prices',
            description: 'Vehicle purchase and rental prices from UEX Corp API.',
            properties: [
                new OA\Property(
                    property: 'purchase',
                    description: 'Purchase prices from UEX Corp.',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'terminal_id', description: 'UEX terminal ID', type: 'integer'),
                            new OA\Property(property: 'terminal_code', type: 'string', nullable: true),
                            new OA\Property(property: 'terminal_name', type: 'string'),
                            new OA\Property(property: 'starmap_location_uuid', type: 'string', nullable: true),
                            new OA\Property(property: 'price_buy', type: 'number', format: 'double'),
                            new OA\Property(property: 'game_version', description: 'Game version this price applies to, e.g. 4.7.1', type: 'string', nullable: true),
                            new OA\Property(property: 'date_updated', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'link', description: 'API URL for the starmap location', type: 'string', nullable: true),
                            new OA\Property(property: 'web_url', description: 'Web URL for the starmap location', type: 'string', nullable: true),
                            new OA\Property(
                                property: 'starmap_location',
                                description: 'Expanded starmap location data',
                                properties: [
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'slug', type: 'string', nullable: true),
                                    new OA\Property(property: 'type_name', type: 'string', nullable: true),
                                    new OA\Property(property: 'parent_name', type: 'string', nullable: true),
                                    new OA\Property(property: 'star_system_name', type: 'string', nullable: true),
                                ],
                                type: 'object',
                                nullable: true
                            ),
                        ],
                        type: 'object'
                    )
                ),
                new OA\Property(
                    property: 'rental',
                    description: 'Rental prices from UEX Corp.',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'terminal_id', description: 'UEX terminal ID', type: 'integer'),
                            new OA\Property(property: 'terminal_code', type: 'string', nullable: true),
                            new OA\Property(property: 'terminal_name', type: 'string'),
                            new OA\Property(property: 'starmap_location_uuid', type: 'string', nullable: true),
                            new OA\Property(property: 'price_rent', type: 'number', format: 'double'),
                            new OA\Property(property: 'game_version', description: 'Game version this price applies to, e.g. 4.7.1', type: 'string', nullable: true),
                            new OA\Property(property: 'date_updated', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'link', description: 'API URL for the starmap location', type: 'string', nullable: true),
                            new OA\Property(property: 'web_url', description: 'Web URL for the starmap location', type: 'string', nullable: true),
                            new OA\Property(
                                property: 'starmap_location',
                                description: 'Expanded starmap location data',
                                properties: [
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'slug', type: 'string', nullable: true),
                                    new OA\Property(property: 'type_name', type: 'string', nullable: true),
                                    new OA\Property(property: 'parent_name', type: 'string', nullable: true),
                                    new OA\Property(property: 'star_system_name', type: 'string', nullable: true),
                                ],
                                type: 'object',
                                nullable: true
                            ),
                        ],
                        type: 'object'
                    )
                ),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'images',
            description: 'Images from external sources for this vehicle.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'source', description: 'Image source identifier', type: 'string', example: 'starcitizen.tools'),
                    new OA\Property(property: 'thumbnail_url', type: 'string', nullable: true),
                    new OA\Property(property: 'thumbnail_width', type: 'integer', nullable: true),
                    new OA\Property(property: 'thumbnail_height', type: 'integer', nullable: true),
                    new OA\Property(property: 'original_url', type: 'string', nullable: true),
                    new OA\Property(property: 'original_width', type: 'integer', nullable: true),
                    new OA\Property(property: 'original_height', type: 'integer', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'updated_at', description: 'Timestamp of last data update.', type: 'string'),
        new OA\Property(property: 'version', description: 'Game version code this data applies to.', type: 'string', example: '4.4.0-LIVE.12340123'),
    ],
    type: 'object'
)]
class VehicleResource extends AbstractBaseResource
{
    use ExpandsUexPrices;
    use ExtractsJsonData;
    use ResolvesGameVersion;

    public static function validIncludes(): array
    {
        return [
            'ports',
            'hardpoints',
            'components',
        ];
    }

    public function toArray(Request $request): array
    {
        if ($this->vehicle?->uuid === null) {
            return [];
        }

        $vehicleData = $this->resource;

        $payload = $vehicleData->data ?? [];
        $flight = $this->extractFromVehicleJson($vehicleData, 'FlightCharacteristics', []);

        $apiVersion = $this->getApiVersion($request);

        $hardpoints = $apiVersion === 'v2'
            ? HardpointResource::collection($this->extractFromVehicleJson($vehicleData, 'Loadout', []))
            : PortResource::collection($this->extractFromVehicleJson($vehicleData, 'Loadout', []));

        $portKey = $apiVersion === 'v2' ? 'hardpoints' : 'ports';

        $cargoGridPayload = $this->extractFromVehicleJson($vehicleData, 'CargoGrids', []);
        $cargoLimits = $this->calculateCargoGridSizeLimits($cargoGridPayload);

        $weaponSnapshot = $this->extractFromVehicleJson($vehicleData, 'Loadout', []);
        if (! empty($weaponSnapshot)) {
            $weaponSnapshot = app(WeaponSnapshotService::class)->compute($weaponSnapshot);
        } else {
            $weaponSnapshot = null;
        }

        $mannedTurrets = $this->decorateTurretEntries($this->extractFromVehicleJson($vehicleData, 'MannedTurrets', []), 'manned');
        $remoteTurrets = $this->decorateTurretEntries($this->extractFromVehicleJson($vehicleData, 'RemoteTurrets', []), 'remote');
        $pdcTurrets = $this->decorateTurretEntries($this->extractFromVehicleJson($vehicleData, 'PdcTurrets', []), 'pdc');

        $this->addMetadata('deprecated_fields', [
            'sizes' => 'Use length, width, and height properties from dimension instead',
            'emission' => 'Use properties from signature instead. Emission.ir currently maps to IR with shields active. Emission.em_max maps to EM Signature with quantum drive active. Emission.em_idle maps to EM Signature with shields active.',
            'mass' => 'Use mass_total property instead. Mass is equal to mass_hull.',
            'shield_hp' => 'Use shield.hp property instead.',
            'shield_face_type' => 'Use shield.face_type property instead.',

            'personal_inventory' => 'No replacement.',

            "{$portKey}[].equipped_item.$portKey" => "Use {$portKey}[].$portKey instead.",

            'armor.signal_infrared' => 'Use armor.signal_multiplier.infrared instead.',
            'armor.signal_electromagnetic' => 'Use armor.signal_multiplier.electromagnetic instead.',
            'armor.signal_cross_section' => 'Use armor.signal_multiplier.cross_section instead.',
            'armor.damage_physical' => 'Use armor.damage_multiplier.physical instead.',
            'armor.damage_energy' => 'Use armor.damage_multiplier.energy instead.',
            'armor.damage_distortion' => 'Use armor.damage_multiplier.distortion instead.',
            'armor.damage_thermal' => 'Use armor.damage_multiplier.thermal instead.',
            'armor.damage_biochemical' => 'Use armor.damage_multiplier.biochemical instead.',
            'armor.damage_stun' => 'Use armor.damage_multiplier.stun instead.',
            'armor.signal_multipliers' => 'Use armor.signal_multiplier instead.',
            'armor.damage_multipliers' => 'Use armor.damage_multiplier instead.',
            'armor.resistance_multipliers' => 'Use armor.resistance_multiplier instead.',
        ]);

        return [
            'uuid' => $this->vehicle->uuid,
            'name' => $vehicleData->display_name ?? $vehicleData->name,
            'game_name' => $vehicleData->name,
            'slug' => $this->vehicle->slug,
            'class_name' => $vehicleData->class_name,

            'sizes' => [
                'length' => $vehicleData->length ?? Arr::get($payload, 'Length'),
                'beam' => $vehicleData->width ?? Arr::get($payload, 'Width'),
                'height' => $vehicleData->height ?? Arr::get($payload, 'Height'),
            ],
            'dimension' => [
                'length' => Arr::get($payload, 'Length'),
                'width' => Arr::get($payload, 'Width'),
                'height' => Arr::get($payload, 'Height'),
            ],
            'emission' => [
                'ir' => Arr::get($payload, 'Emission.IrShields'),
                'em_idle' => Arr::get($payload, 'Emission.EmShields'),
                'em_max' => Arr::get($payload, 'Emission.EmQuantum'),
            ],

            'mass' => $vehicleData->mass ?? Arr::get($payload, 'Mass'),

            'mass_hull' => $vehicleData->mass_vehicle ?? Arr::get($payload, 'Mass'),
            'mass_loadout' => $vehicleData->mass_loadout ?? Arr::get($payload, 'MassLoadout'),
            'mass_total' => $vehicleData->mass_total ?? Arr::get($payload, 'MassTotal'),

            'cargo_capacity' => $vehicleData->cargo ?? Arr::get($payload, 'Cargo'),
            'cargo_grids' => ItemInventoryResource::collection(Arr::get($payload, 'CargoGrids', [])),
            $this->mergeWhen(
                ! empty($cargoLimits),
                fn () => ['cargo_limits' => $cargoLimits]
            ),
            'vehicle_inventory' => Arr::get($payload, 'Stowage', 0) * (10 ** 6),
            'inventory_containers' => ItemInventoryResource::collection(Arr::get($payload, 'InventoryContainers', [])),

            'crew' => [
                'min' => Arr::get($payload, 'Crew'),
                'max' => Arr::get($payload, 'Crew'),
                'weapon' => Arr::get($payload, 'WeaponCrew'),
                'operation' => null,
            ],

            'max_medical_tier' => $this->resolveMaxMedicalTier($vehicleData),

            'seating' => [
                'crew_stations' => $this->extractFromVehicleJson($vehicleData, 'Seating.CrewStations', 0),
                'ejection_seats' => $this->extractFromVehicleJson($vehicleData, 'Seating.EjectionSeats', 0),
                'escape_pods' => $this->extractFromVehicleJson($vehicleData, 'Seating.EscapePods'),
                'jump_seats' => $this->extractFromVehicleJson($vehicleData, 'Seating.JumpSeats'),
                'beds' => $this->extractFromVehicleJson($vehicleData, 'Seating.TotalBeds', 0),
                'medical_beds' => $this->resolveMedicalBeds($vehicleData),
            ],

            'health' => Arr::get($payload, 'Health', 0),

            'shield_hp' => Arr::get($payload, 'ShieldsTotal.Hp'),
            'shield_face_type' => Arr::get($payload, 'ShieldController.FaceType'),

            'shield' => [
                'hp' => Arr::get($payload, 'ShieldsTotal.Hp', 0),
                'regeneration' => Arr::get($payload, 'ShieldsTotal.Regen'),
                'face_type' => Arr::get($payload, 'ShieldController.FaceType'),
                'max_reallocation' => Arr::get($payload, 'ShieldController.MaxReallocation'),
                'reconfiguration_cooldown' => Arr::get($payload, 'ShieldController.ReconfigurationCooldown'),
                'max_electrical_charge_damage_rate' => Arr::get($payload, 'ShieldController.MaxElectricalChargeDamageRate'),
                'resistance' => $this->buildDamageTypeRange($vehicleData, 'ShieldsTotal.Resistance'),
                'absorption' => $this->buildDamageTypeRange($vehicleData, 'ShieldsTotal.Absorption'),
            ],

            $this->mergeWhen(
                $weaponSnapshot !== null && $this->isVehicleShowRoute($request),
                fn () => ['weapon_snapshot' => $weaponSnapshot]
            ),

            'speed' => $this->buildSpeed($flight),

            'afterburner' => [
                'pitch_boost_multiplier' => Arr::get($flight, 'Afterburner.AngularAccelerationMultiplier.Pitch'),
                'roll_boost_multiplier' => Arr::get($flight, 'Afterburner.AngularAccelerationMultiplier.Roll'),
                'yaw_boost_multiplier' => Arr::get($flight, 'Afterburner.AngularAccelerationMultiplier.Yaw'),

                'capacitor' => Arr::get($flight, 'Afterburner.CapacitorMax'),
                'idle_cost' => Arr::get($flight, 'Afterburner.CapacitorAfterburnerIdleCost'),
                'linear_cost' => Arr::get($flight, 'Afterburner.CapacitorAfterburnerLinearCost'),
                'angular_cost' => Arr::get($flight, 'Afterburner.CapacitorAfterburnerAngularCost'),
                'regen_per_second' => Arr::get($flight, 'Afterburner.CapacitorRegenPerSec'),
                'regen_delay_after_use' => Arr::get($flight, 'Afterburner.CapacitorRegenDelayAfterUse'),
                'pre_delay_time' => Arr::get($flight, 'Afterburner.AfterburnerPreDelayTime'),
                'ramp_up_time' => Arr::get($flight, 'Afterburner.AfterburnerRampUpTime'),
                'ramp_down_time' => Arr::get($flight, 'Afterburner.AfterburnerRampDownTime'),
                'regen_time' => Arr::get($flight, 'Afterburner.RegenTime'),
                'regen_delay' => Arr::get($flight, 'Afterburner.CapacitorRegenDelayAfterUse'),
            ],

            'fuel' => $this->buildFuel($vehicleData, $payload),
            'propulsion' => $this->buildPropulsion($vehicleData),
            'quantum' => $this->buildQuantum($vehicleData),

            'agility' => $this->buildAgility($flight),

            'armor' => $this->buildArmor($vehicleData),

            $this->mergeWhen(
                $this->extractFromVehicleJson($vehicleData, 'Weaponry.PilotDps') !== null
                || $this->extractFromVehicleJson($vehicleData, 'Weaponry.FixedWeapons') !== null
                || $this->extractFromVehicleJson($vehicleData, 'Weaponry.Missiles') !== null,
                fn () => ['weaponry' => $this->buildWeaponry($vehicleData)]
            ),

            'manufacturer' => new ManufacturerLinkResource($vehicleData->manufacturer),
            'size_class' => $vehicleData->size ?? Arr::get($payload, 'Size'),

            'cross_section' => [
                'length' => Arr::get($payload, 'CrossSection.X'),
                'width' => Arr::get($payload, 'CrossSection.Y'),
                'height' => Arr::get($payload, 'CrossSection.Z'),
            ],
            'cross_section_max' => max(
                Arr::get($payload, 'CrossSection.X', 0),
                Arr::get($payload, 'CrossSection.Y', 0),
                Arr::get($payload, 'CrossSection.Z', 0),
            ) ?: null,

            'is_vehicle' => Arr::get($payload, 'IsVehicle'),
            'is_gravlev' => Arr::get($payload, 'IsGravlev'),
            'is_spaceship' => Arr::get($payload, 'IsSpaceship'),

            'signature' => [
                'ir_quantum' => Arr::get($payload, 'Emission.IrQuantum'),
                'ir_shields' => Arr::get($payload, 'Emission.IrShields'),

                'em_quantum' => Arr::get($payload, 'Emission.EmQuantum'),
                'em_shields' => Arr::get($payload, 'Emission.EmShields'),

                'em_groups_quantum' => Arr::get($payload, 'Emission.EmGroupsQuantum'),
                'em_groups_shields' => Arr::get($payload, 'Emission.EmGroupsShields'),

                'em_segment_groups_quantum' => Arr::get($payload, 'Emission.EmSegmentGroupsQuantum'),
                'em_segment_groups_shields' => Arr::get($payload, 'Emission.EmSegmentGroupsShields'),

                'em_per_segment' => Arr::get($payload, 'Emission.EmPerSegment'),
            ],

            'cooling' => [
                'generation_segments' => Arr::get($payload, 'Cooling.GenerationSegments'),
                'usage_shields_pct' => Arr::get($payload, 'Cooling.UsedSegmentsShieldsPct'),
                'usage_quantum_pct' => Arr::get($payload, 'Cooling.UsedSegmentsQuantumPct'),

                'used_segments_shields' => Arr::get($payload, 'Cooling.UsedSegmentsShields'),
                'used_segments_quantum' => Arr::get($payload, 'Cooling.UsedSegmentsQuantum'),

                'used_segments_shields_grouped' => Arr::get($payload, 'Cooling.UsedSegmentsShieldsGrouped'),
                'used_segments_quantum_grouped' => Arr::get($payload, 'Cooling.UsedSegmentsQuantumGrouped'),
            ],

            'power' => [
                'generation_segments' => Arr::get($payload, 'Power.GenerationSegments'),
                'used_segments_shields' => Arr::get($payload, 'Power.UsedSegmentsShields'),
                'used_segments_quantum' => Arr::get($payload, 'Power.UsedSegmentsQuantum'),
                'used_segments_grouped' => Arr::get($payload, 'Power.UsedSegmentsGrouped'),
            ],

            $this->mergeWhen(
                ! empty(Arr::get($payload, 'PowerPools')),
                fn () => ['power_pools' => $this->buildPowerPools($vehicleData)]
            ),

            'penetration_multiplier' => [
                'fuse' => Arr::get($payload, 'PenetrationMultiplier.Fuse'),
                'components' => Arr::get($payload, 'PenetrationMultiplier.Components'),
            ],

            'insurance' => [
                'claim_time' => Arr::get($payload, 'Insurance.StandardClaimTime'),
                'expedite_time' => Arr::get($payload, 'Insurance.ExpeditedClaimTime'),
                'expedite_cost' => Arr::get($payload, 'Insurance.ExpeditedCost'),
            ],
            'damage_limits' => [
                'before_destruction' => Arr::get($payload, 'DamageBeforeDestruction'),
                'before_detach' => Arr::get($payload, 'DamageBeforeDetach'),
            ],
            $this->mergeWhen(
                $this->isVehicleShowRoute($request),
                fn () => [$portKey => $hardpoints]
            ),
            'parts' => PartResource::collection(Arr::get($payload, 'Parts', [])),
            'turrets' => [
                'manned' => TurretSummaryResource::collection($mannedTurrets),
                'remote' => TurretSummaryResource::collection($remoteTurrets),
                'pdc' => TurretSummaryResource::collection($pdcTurrets),
            ],

            'career' => $vehicleData->career ?? Arr::get($payload, 'Career'),
            'role' => $vehicleData->role ?? Arr::get($payload, 'Role'),

            $this->mergeWhen(
                $this->relationLoaded('shipMatrixVehicle') && $this->shipMatrixVehicle?->relationLoaded('components') && $this->isVehicleShowRoute($request),
                fn () => ['components' => $this->getComponents($vehicleData)]
            ),

            'web_url' => $this->buildWebUrl($request),
            'link' => $this->buildApiUrl($request),

            'loaner' => $this->getLoaner($vehicleData),
            'skus' => $this->getSkus($vehicleData),
            'msrp' => $vehicleData->relationLoaded('shipMatrixVehicle')
                ? $vehicleData->shipMatrixVehicle?->msrp
                : null,
            'pledge_url' => $vehicleData->relationLoaded('shipMatrixVehicle')
                ? $vehicleData->shipMatrixVehicle?->pledge_url
                : null,

            'uex_prices' => [
                'purchase' => $this->expandVehiclePrices($vehicleData, 'uex_purchase_prices', 'price_buy'),
                'rental' => $this->expandVehiclePrices($vehicleData, 'uex_rental_prices', 'price_rent'),
            ],

            'images' => $this->vehicle->images ?? [],

            'updated_at' => $vehicleData->updated_at,
            'version' => $vehicleData->relationLoaded('gameVersion')
                ? $vehicleData->gameVersion?->code
                : null,

            ...$this->resolveShipMatrixData($vehicleData, $request),
        ];
    }

    private function buildSpeed(array $flight): array
    {
        return [
            'scm' => Arr::get($flight, 'Speeds.Scm'),
            'max' => Arr::get($flight, 'Speeds.Max'),
            'boost_forward' => Arr::get($flight, 'Speeds.BoostForward'),
            'boost_backward' => Arr::get($flight, 'Speeds.BoostBackward'),
            'zero_to_scm' => Arr::get($flight, 'Timing.ZeroToScm'),
            'zero_to_max' => Arr::get($flight, 'Timing.ZeroToMax'),
            'scm_to_zero' => Arr::get($flight, 'Timing.ScmToZero'),
            'max_to_zero' => Arr::get($flight, 'Timing.MaxToZero'),
        ];
    }

    private function buildAgility(array $flight): array
    {
        return [
            'pitch' => Arr::get($flight, 'AngularRates.Pitch'),
            'yaw' => Arr::get($flight, 'AngularRates.Yaw'),
            'roll' => Arr::get($flight, 'AngularRates.Roll'),
            'pitch_boosted' => Arr::get($flight, 'AngularRatesBoosted.Pitch'),
            'yaw_boosted' => Arr::get($flight, 'AngularRatesBoosted.Yaw'),
            'roll_boosted' => Arr::get($flight, 'AngularRatesBoosted.Roll'),

            'acceleration' => array_filter([
                'main' => Arr::get($flight, 'Acceleration.Raw.Forward'),
                'retro' => Arr::get($flight, 'Acceleration.Raw.Backward'),
                'vtol' => Arr::get($flight, 'Acceleration.Raw.Vtol'),
                'maneuvering' => Arr::get($flight, 'Acceleration.Raw.Maneuvering'),

                'main_g' => Arr::get($flight, 'Acceleration.RawG.Forward'),
                'retro_g' => Arr::get($flight, 'Acceleration.RawG.Backward'),
                'vtol_g' => Arr::get($flight, 'Acceleration.RawG.Vtol'),
                'maneuvering_g' => Arr::get($flight, 'Acceleration.RawG.Maneuvering'),
            ], static fn ($value) => $value !== null),
        ];
    }

    private function buildFuel(VehicleData $vehicleData, Collection $payload): array
    {
        return [
            'capacity' => $this->extractFromVehicleJson($vehicleData, 'Propulsion.FuelCapacity', 0) / 1000,
            'intake_rate' => $this->extractFromVehicleJson($vehicleData, 'Propulsion.FuelIntakeRate'),
            'usage' => [
                'main' => $this->extractFromVehicleJson($vehicleData, 'Propulsion.FuelUsage.Main'),
                'retro' => $this->extractFromVehicleJson($vehicleData, 'Propulsion.FuelUsage.Retro'),
                'vtol' => $this->extractFromVehicleJson($vehicleData, 'Propulsion.FuelUsage.Vtol'),
                'maneuvering' => $this->extractFromVehicleJson($vehicleData, 'Propulsion.FuelUsage.Maneuvering'),
            ],
        ];
    }

    private function buildQuantum(VehicleData $vehicleData): array
    {
        return [
            'quantum_speed' => $this->extractFromVehicleJson($vehicleData, 'QuantumTravel.Speed'),
            'quantum_spool_time' => $this->extractFromVehicleJson($vehicleData, 'QuantumTravel.SpoolTime'),
            'quantum_fuel_capacity' => $this->extractFromVehicleJson($vehicleData, 'QuantumTravel.FuelCapacity', 0) / 1000,
            'quantum_range' => $this->extractFromVehicleJson($vehicleData, 'QuantumTravel.Range'),
            'port_olisar_to_arccorp_time' => $this->extractFromVehicleJson($vehicleData, 'QuantumTravel.PortOlisarToArcCorpTime'),
            'port_olisar_to_arccorp_fuel' => $this->extractFromVehicleJson($vehicleData, 'QuantumTravel.PortOlisarToArcCorpFuel'),
        ];
    }

    private function buildPowerPools(VehicleData $vehicleData): array
    {
        $powerPools = $this->extractFromVehicleJson($vehicleData, 'PowerPools', []);

        return array_map(static fn ($poolData) => [
            'type' => Arr::get($poolData, 'Type'),
            'item_type' => Arr::get($poolData, 'ItemType'),
            'size' => Arr::get($poolData, 'Size'),
        ], $powerPools);
    }

    /**
     * @param  array<int, mixed>  $entries
     * @return array<int, array<string, mixed>>
     */
    private function decorateTurretEntries(array $entries, string $category): array
    {
        return collect($entries)
            ->filter(static fn (mixed $entry): bool => is_array($entry))
            ->map(static fn (array $entry): array => [
                ...$entry,
                'Category' => $category,
            ])
            ->values()
            ->all();
    }

    private function buildWebUrl(Request $request): string
    {
        return $this->urlWithVersion(
            route('web.vehicles.show', ['vehicle' => $this->vehicle->slug ?? $this->vehicle->uuid]),
            $request,
        );
    }

    private function buildApiUrl(Request $request): string
    {
        $identifier = $this->vehicle->uuid ?? $this->resource->name;

        return $this->urlWithVersion(
            route('vehicles.show', ['vehicle' => $identifier]),
            $request,
        );
    }

    private function buildArmor(VehicleData $vehicleData): array
    {
        $armorUuid = $this->extractFromVehicleJson($vehicleData, 'Armor.UUID');

        if ($armorUuid === null) {
            return [];
        }

        $item = $this->loadItemForVersion($armorUuid);

        if ($item === null) {
            return [];
        }

        $itemData = $item->data->first();

        if ($itemData === null) {
            return [];
        }

        return $this->buildArmorFromItemData($armorUuid, $itemData);
    }

    /**
     * Build armor data from resolved ItemData using ArmorResource.
     *
     * Outputs canonical ArmorResource format with deprecated plural key aliases
     * for backward compatibility during transition.
     *
     * @return array<string, mixed>
     */
    private function buildArmorFromItemData(string $uuid, ItemData $itemData): array
    {
        $armor = (new ArmorResource($itemData))->resolve(request());
        $armor['uuid'] = $uuid;

        // Deprecated plural aliases for backward compatibility
        $armor['signal_multipliers'] = [
            'cross_section' => $armor['signal_multiplier']['cross_section'] ?? null,
            'infrared' => $armor['signal_multiplier']['infrared'] ?? null,
            'electromagnetic' => $armor['signal_multiplier']['electromagnetic'] ?? null,
        ];
        $armor['damage_multipliers'] = [
            'physical' => $armor['damage_multiplier']['physical'] ?? null,
            'energy' => $armor['damage_multiplier']['energy'] ?? null,
            'distortion' => $armor['damage_multiplier']['distortion'] ?? null,
            'thermal' => $armor['damage_multiplier']['thermal'] ?? null,
            'biochemical' => $armor['damage_multiplier']['biochemical'] ?? null,
            'stun' => $armor['damage_multiplier']['stun'] ?? null,
        ];
        $armor['resistance_multipliers'] = [
            'physical' => $armor['resistance_multiplier']['physical'] ?? null,
            'energy' => $armor['resistance_multiplier']['energy'] ?? null,
            'distortion' => $armor['resistance_multiplier']['distortion'] ?? null,
            'thermal' => $armor['resistance_multiplier']['thermal'] ?? null,
            'biochemical' => $armor['resistance_multiplier']['biochemical'] ?? null,
            'stun' => $armor['resistance_multiplier']['stun'] ?? null,
        ];

        return $armor;
    }

    private function resolveShipMatrixData(VehicleData $vehicleData, Request $request): array
    {
        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return [];
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle->exists) {
            return [];
        }

        $matrixVehicle = new \App\Http\Resources\StarCitizen\Vehicle\VehicleResource($shipMatrixVehicle)
            ->resolve($request);

        $fieldMap = [
            'id' => 'id',
            'chassis_id' => 'chassis_id',
            'name' => 'shipmatrix_name',
            'foci' => 'foci',
            'production_status' => 'production_status',
            'production_note' => 'production_note',
            'type' => 'type',
            'description' => 'description',
            'size' => 'size',
            'msrp' => 'msrp',
            'pledge_url' => 'pledge_url',
            'loaner' => 'loaner',
            'skus' => 'skus',
            'components' => 'components',
        ];

        $result = [];

        foreach ($fieldMap as $sourceKey => $targetKey) {
            if (array_key_exists($sourceKey, $matrixVehicle) && $matrixVehicle[$sourceKey] !== null) {
                $result[$targetKey] = $matrixVehicle[$sourceKey];
            }
        }

        return $result;
    }

    /**
     * Get components for the ship-matrix vehicle.
     */
    private function getComponents(VehicleData $vehicleData): array
    {
        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return [];
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle->exists) {
            return [];
        }

        if (! $shipMatrixVehicle->relationLoaded('components')) {
            return [];
        }

        return ComponentResource::collection($shipMatrixVehicle->components)->resolve();
    }

    private function isVehicleShowRoute(Request $request): bool
    {
        return $request->routeIs('vehicles.show') || $request->routeIs('*.vehicles.show');
    }

    private function getApiVersion(Request $request): ?string
    {
        return $request->route('api_version');
    }

    private function getLoaner(VehicleData $vehicleData): array
    {
        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return [];
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle || ! $shipMatrixVehicle->exists) {
            return [];
        }

        if (! $shipMatrixVehicle->relationLoaded('loaner')) {
            return [];
        }

        return VehicleLoanerResource::collection($shipMatrixVehicle->loaner)->resolve();
    }

    private function getSkus(VehicleData $vehicleData): array
    {
        if (! $vehicleData->relationLoaded('shipMatrixVehicle')) {
            return [];
        }

        $shipMatrixVehicle = $vehicleData->shipMatrixVehicle;

        if (! $shipMatrixVehicle || ! $shipMatrixVehicle->exists) {
            return [];
        }

        if (! $shipMatrixVehicle->relationLoaded('skus')) {
            return [];
        }

        return VehicleSkuResource::collection($shipMatrixVehicle->skus)->resolve();
    }

    private function expandVehiclePrices(VehicleData $vehicleData, string $column, string $priceField): array
    {
        return $this->expandPrices((array) ($vehicleData->$column ?? []));
    }

    public function calculateCargoGridSizeLimits(array $cargoGrids): ?array
    {
        $minSize = collect($cargoGrids)
            ->map(fn (array $grid) => $this->extractSizeBlock($grid, 'MinSize', 'min_size'))
            ->filter()
            ->sortBy(fn (array $size) => $size['x'] * $size['y'] * $size['z'])
            ->first();

        $maxSize = collect($cargoGrids)
            ->map(fn (array $grid) => $this->extractSizeBlock($grid, 'MaxSize', 'max_size'))
            ->filter()
            ->sortByDesc(fn (array $size) => $size['x'] * $size['y'] * $size['z'])
            ->first();

        $limits = array_filter([
            'min_size' => $minSize,
            'max_size' => $maxSize,
        ], static fn ($value) => $value !== null);

        return $limits === [] ? null : $limits;
    }

    /**
     * @param  array<string, mixed>  $grid
     * @return array{x: float|int, y: float|int, z: float|int}|null
     */
    private function extractSizeBlock(array $grid, string $key, string $fallbackKey): ?array
    {
        $data = Arr::get($grid, $key);

        if (! is_array($data)) {
            $data = Arr::get($grid, $fallbackKey);
        }

        if (! is_array($data)) {
            return null;
        }

        $x = Arr::get($data, 'X', Arr::get($data, 'x'));
        $y = Arr::get($data, 'Y', Arr::get($data, 'y'));
        $z = Arr::get($data, 'Z', Arr::get($data, 'z'));

        if ($x === null || $y === null || $z === null) {
            return null;
        }

        return [
            'x' => $x,
            'y' => $y,
            'z' => $z,
        ];
    }

    private function resolveMaxMedicalTier(VehicleData $vehicleData): ?string
    {
        return collect($this->extractFromVehicleJson($vehicleData, 'Seating.MedicalBeds'))
            ->pluck('Tier')
            ->sortByDesc(static fn (string $tier): int => (int) ltrim($tier, 'T'))
            ->first();
    }

    private function resolveMedicalBeds(VehicleData $vehicleData): ?array
    {
        $medicalBeds = $this->extractFromVehicleJson($vehicleData, 'Seating.MedicalBeds');

        if ($medicalBeds === null || $medicalBeds === []) {
            return null;
        }

        return collect($medicalBeds)->pluck('Count', 'Tier')->all();
    }

    /**
     * Build damage-type range data (minimum/maximum per type).
     * Used for shield resistance and absorption.
     *
     * @return array<string, array{minimum: mixed, maximum: mixed}>
     */
    private function buildDamageTypeRange(VehicleData $vehicleData, string $path): array
    {
        $damageTypes = ['Physical', 'Energy', 'Distortion', 'Thermal', 'Biochemical', 'Stun'];

        $result = [];
        foreach ($damageTypes as $type) {
            $result[strtolower($type)] = [
                'minimum' => $this->extractFromVehicleJson($vehicleData, "{$path}.{$type}.Minimum"),
                'maximum' => $this->extractFromVehicleJson($vehicleData, "{$path}.{$type}.Maximum"),
            ];
        }

        return $result;
    }

    private function buildPropulsion(VehicleData $vehicleData): array
    {
        $thrusters = $this->extractFromVehicleJson($vehicleData, 'Propulsion.Thrusters', []);

        $thrustCapacityRaw = $this->extractFromVehicleJson($vehicleData, 'Propulsion.ThrustCapacity');
        $thrustCapacity = null;
        if (is_array($thrustCapacityRaw)) {
            $thrustCapacity = array_filter([
                'main' => Arr::get($thrustCapacityRaw, 'Main'),
                'retro' => Arr::get($thrustCapacityRaw, 'Retro'),
                'vtol' => Arr::get($thrustCapacityRaw, 'Vtol'),
                'maneuvering' => Arr::get($thrustCapacityRaw, 'Maneuvering'),
            ], static fn (mixed $value): bool => $value !== null);

            if ($thrustCapacity === []) {
                $thrustCapacity = null;
            }
        }

        return [
            'thrusters' => array_map(static fn (array $thruster): array => [
                'type' => Arr::get($thruster, 'Type'),
                'count' => Arr::get($thruster, 'Count'),
                'capacity' => Arr::get($thruster, 'Capacity'),
                'g' => Arr::get($thruster, 'G'),
            ], $thrusters),
            'thrust_capacity' => $thrustCapacity,
        ];
    }

    private function buildWeaponry(VehicleData $vehicleData): array
    {
        $result = array_filter([
            'pilot_dps' => $this->extractFromVehicleJson($vehicleData, 'Weaponry.PilotDps'),
            'pilot_alpha' => $this->extractFromVehicleJson($vehicleData, 'Weaponry.PilotAlpha'),
            'pilot_sustained_dps' => $this->extractFromVehicleJson($vehicleData, 'Weaponry.PilotSustainedDps'),
            'turret_dps' => $this->extractFromVehicleJson($vehicleData, 'Weaponry.TurretDps'),
            'turret_alpha' => $this->extractFromVehicleJson($vehicleData, 'Weaponry.TurretAlpha'),
            'turret_sustained_dps' => $this->extractFromVehicleJson($vehicleData, 'Weaponry.TurretSustainedDps'),
        ], static fn (mixed $value): bool => $value !== null);

        $fixedWeapons = $this->extractFromVehicleJson($vehicleData, 'Weaponry.FixedWeapons');
        if (is_array($fixedWeapons)) {
            $result['fixed_weapons'] = [
                'dps_total' => Arr::get($fixedWeapons, 'DpsTotal'),
                'sustained_dps_total' => Arr::get($fixedWeapons, 'SustainedDpsTotal'),
                'alpha_total' => Arr::get($fixedWeapons, 'AlphaTotal'),
                'weapons' => array_map(static fn (array $weapon): array => [
                    'name' => Arr::get($weapon, 'Name'),
                    'dps' => Arr::get($weapon, 'Dps'),
                    'sustained_dps' => Arr::get($weapon, 'SustainedDps'),
                    'alpha' => Arr::get($weapon, 'Alpha'),
                ], Arr::get($fixedWeapons, 'Weapons', [])),
            ];
        }

        $missiles = $this->extractFromVehicleJson($vehicleData, 'Weaponry.Missiles');
        if (is_array($missiles)) {
            $result['missiles'] = [
                'count' => Arr::get($missiles, 'Count'),
                'damage' => [
                    'physical' => Arr::get($missiles, 'Damage.Physical'),
                    'energy' => Arr::get($missiles, 'Damage.Energy'),
                    'distortion' => Arr::get($missiles, 'Damage.Distortion'),
                    'thermal' => Arr::get($missiles, 'Damage.Thermal'),
                    'biochemical' => Arr::get($missiles, 'Damage.Biochemical'),
                    'stun' => Arr::get($missiles, 'Damage.Stun'),
                    'total' => Arr::get($missiles, 'Damage.Total'),
                ],
            ];
        }

        $totalMissiles = $this->extractFromVehicleJson($vehicleData, 'Weaponry.TotalMissiles');
        if ($totalMissiles !== null) {
            $result['total_missile_damage'] = $totalMissiles;
        }

        return $result;
    }
}
