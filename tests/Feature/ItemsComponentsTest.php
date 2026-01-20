<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

describe('Items Components', function () {
    // ==================== SIMPLE COMPONENTS ====================

    describe('Missile Rack Card', function () {
        it('renders missile rack with full data', function () {
            $data = [
                'missile_count' => 4,
                'missile_size' => 2,
            ];

            $output = Blade::render('<x-items.missile-rack-card :missileRack="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Missile Rack')
                ->toContain('Missile Count')
                ->toContain('4')
                ->toContain('Missile Size')
                ->toContain('Size 2');
        });

        it('handles empty missile rack data', function () {
            $data = [];

            $output = Blade::render('<x-items.missile-rack-card :missileRack="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Missile Rack')
                ->not->toContain('Missile Count')
                ->not->toContain('Missile Size');
        });
    });

    describe('Counter Measure Card', function () {
        it('renders counter measure with signature data', function () {
            $data = [
                'type' => 'Flare',
                'signature' => [
                    'infrared' => 150.5,
                    'cross_section' => 10.2,
                    'electromagnetic' => 5.0,
                    'decibel' => 80.0,
                ],
            ];

            $output = Blade::render('<x-items.counter-measure-card :counterMeasure="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Counter Measure Specifications')
                ->toContain('Type')
                ->toContain('Flare')
                ->toContain('Signature')
                ->toContain('Infrared')
                ->toContain('150.50')
                ->toContain('Cross Section')
                ->toContain('10.20');
        });

        it('renders counter measure without signature', function () {
            $data = ['type' => 'Chaff'];

            $output = Blade::render('<x-items.counter-measure-card :counterMeasure="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Counter Measure Specifications')
                ->toContain('Type')
                ->toContain('Chaff')
                ->not->toContain('Signature');
        });
    });

    describe('Emission Card', function () {
        it('renders emission with all data', function () {
            $data = [
                'ir' => 250,
                'em_min' => 10,
                'em_max' => 50,
                'em_decay' => 5,
                'em_per_segment' => 3,
            ];

            $output = Blade::render('<x-items.emission-card :emission="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Emission')
                ->toContain('IR Emission')
                ->toContain('250')
                ->toContain('EM Range')
                ->toContain('10 - 50')
                ->toContain('EM Decay')
                ->toContain('5')
                ->toContain('EM Per Segment')
                ->toContain('3');
        });

        it('renders emission with partial data', function () {
            $data = ['ir' => 300];

            $output = Blade::render('<x-items.emission-card :emission="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('IR Emission')
                ->toContain('300')
                ->not->toContain('EM Range')
                ->not->toContain('EM Decay');
        });
    });

    describe('EMP Card', function () {
        it('renders EMP generator with full data', function () {
            $data = [
                'distortion_damage' => 500.5,
                'emp_radius' => 1000.0,
                'min_emp_radius' => 50.0,
                'charge_duration' => 5.0,
                'unleash_duration' => 2.5,
                'cooldown_duration' => 30.0,
            ];

            $output = Blade::render('<x-items.emp-card :emp="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('EMP Generator Specifications')
                ->toContain('Distortion Damage')
                ->toContain('500.50')
                ->toContain('EMP Radius')
                ->toContain('1,000.00 m')
                ->toContain('Minimum EMP Radius')
                ->toContain('50.00 m')
                ->toContain('Charge Duration')
                ->toContain('5.00 s');
        });

        it('handles empty EMP data', function () {
            $data = [];

            $output = Blade::render('<x-items.emp-card :emp="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('EMP Generator Specifications')
                ->not->toContain('Distortion Damage')
                ->not->toContain('EMP Radius');
        });
    });

    describe('Radiation Resistance Card', function () {
        it('renders radiation resistance with full data', function () {
            $data = [
                'maximum_radiation_capacity' => 500.0,
                'radiation_dissipation_rate' => 10.5,
            ];

            $output = Blade::render('<x-items.radiation-resistance-card :radiationResistance="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Radiation Resistance')
                ->toContain('Maximum Radiation Capacity')
                ->toContain('500.00 REM')
                ->toContain('Radiation Dissipation Rate')
                ->toContain('10.50 REM/s');
        });

        it('handles null radiation resistance data', function () {
            $data = [];

            $output = Blade::render('<x-items.radiation-resistance-card :radiationResistance="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Radiation Resistance')
                ->not->toContain('Maximum Radiation Capacity');
        });
    });

    describe('Temperature Resistance Card', function () {
        it('renders temperature resistance with full data', function () {
            $data = [
                'minimum' => -50.0,
                'maximum' => 150.0,
            ];

            $output = Blade::render('<x-items.temperature-resistance-card :temperatureResistance="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Temperature Resistance')
                ->toContain('Minimum Temperature')
                ->toContain('-50.0°C')
                ->toContain('Maximum Temperature')
                ->toContain('150.0°C');
        });

        it('handles null temperature data', function () {
            $data = [];

            $output = Blade::render('<x-items.temperature-resistance-card :temperatureResistance="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Temperature Resistance')
                ->not->toContain('Minimum Temperature');
        });
    });

    describe('Self Destruct Card', function () {
        it('renders self destruct with full data', function () {
            $data = [
                'damage' => 10000,
                'countdown' => 10,
                'radius' => 500,
                'min_radius' => 50,
                'phys_radius' => 100,
                'min_phys_radius' => 10,
            ];

            $output = Blade::render('<x-items.self-destruct-card :selfDestruct="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Self Destruct')
                ->toContain('Damage')
                ->toContain('10000')
                ->toContain('Countdown')
                ->toContain('10s')
                ->toContain('Maximum Radius')
                ->toContain('500m')
                ->toContain('Physical Impact Radius')
                ->toContain('100m');
        });

        it('handles minimal self destruct data', function () {
            $data = ['damage' => 5000];

            $output = Blade::render('<x-items.self-destruct-card :selfDestruct="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Damage')
                ->toContain('5000')
                ->not->toContain('Countdown')
                ->not->toContain('Maximum Radius');
        });
    });

    describe('Shield Controller Card', function () {
        it('renders shield controller with full data', function () {
            $data = [
                'face_type' => '4-Face',
                'max_reallocation' => 2.5,
                'reconfiguration_cooldown' => 5.0,
                'max_electrical_charge_damage_rate' => 100.0,
            ];

            $output = Blade::render('<x-items.shield-controller-card :shieldController="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Shield Controller')
                ->toContain('Face Type')
                ->toContain('4-Face')
                ->toContain('Max Reallocation')
                ->toContain('2.50')
                ->toContain('Reconfiguration Cooldown')
                ->toContain('5.00s');
        });

        it('handles empty shield controller data', function () {
            $data = [];

            $output = Blade::render('<x-items.shield-controller-card :shieldController="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Shield Controller')
                ->not->toContain('Face Type')
                ->not->toContain('Max Reallocation');
        });
    });

    // ==================== MEDIUM COMPLEXITY COMPONENTS ====================

    describe('Ammunition Card', function () {
        it('renders ammunition with full data and damage maps', function () {
            $data = [
                'uuid' => 'test-uuid-123',
                'speed' => 800,
                'lifetime' => 5,
                'range' => 4000,
                'size' => 1,
                'capacity' => 100,
                'initial_capacity' => 100,
                'bullet_type' => 1,
                'penetration' => [
                    'base_distance' => 1000,
                    'near_radius' => 50,
                    'far_radius' => 200,
                    'angle' => 15,
                ],
                'impact_damage_map' => [
                    'physical' => 25.5,
                    'energy' => 10.0,
                    'thermal' => 5.0,
                ],
                'detonation_damage_map' => [
                    'physical' => 50.0,
                    'energy' => 30.0,
                ],
                'explosion_radius' => [
                    'min' => 5.0,
                    'max' => 10.0,
                ],
            ];

            $output = Blade::render('<x-items.ammunition-card :ammunition="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Ammunition')
                ->toContain('Speed')
                ->toContain('800 m/s')
                ->toContain('Penetration')
                ->toContain('Impact Damage')
                ->toContain('Physical')
                ->toContain('25')
                ->toContain('Detonation Damage')
                ->toContain('50')
                ->toContain('Explosion Radius')
                ->toContain('Minimum');
        });

        it('hides collapse sections when data is empty', function () {
            $data = ['speed' => 800];

            $output = Blade::render('<x-items.ammunition-card :ammunition="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('800 m/s')
                ->not->toContain('Penetration')
                ->not->toContain('Impact Damage')
                ->not->toContain('Detonation Damage');
        });
    });

    describe('Bomb Card', function () {
        it('renders bomb with full data', function () {
            $data = [
                'arm_time' => 2.5,
                'ignite_time' => 5.0,
                'collision_delay_time' => 1.0,
                'maximum_drop_angle' => 45.0,
                'damage_total' => 1000.0,
                'explosion' => [
                    'requires_launcher' => true,
                    'radius_min' => 10.0,
                    'radius_max' => 50.0,
                    'safety_distance' => 100.0,
                    'proximity' => 5.0,
                ],
                'damage_map' => [
                    'physical' => 500,
                    'energy' => 300,
                    'thermal' => 200,
                ],
            ];

            $output = Blade::render('<x-items.bomb-card :bomb="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Bomb Specifications')
                ->toContain('Arm Time')
                ->toContain('2.50 s')
                ->toContain('Explosion')
                ->toContain('Requires Launcher')
                ->toContain('Yes')
                ->toContain('Minimum Radius')
                ->toContain('10.00 m')
                ->toContain('Damage Map')
                ->toContain('Physical')
                ->toContain('500.00');
        });

        it('renders bomb without explosion and damage map', function () {
            $data = [
                'arm_time' => 2.0,
                'damage_total' => 500.0,
            ];

            $output = Blade::render('<x-items.bomb-card :bomb="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Arm Time')
                ->toContain('2.00 s')
                ->not->toContain('Explosion')
                ->not->toContain('Damage Map');
        });
    });

    describe('Radar Card', function () {
        it('renders radar with all sensitivity data', function () {
            $data = [
                'cooldown' => 2.5,
                'sensitivity' => [
                    'infrared' => 150.0,
                    'cross_section' => 25.5,
                    'electromagnetic' => 10.0,
                    'resource' => 5.0,
                    'db' => -80.0,
                ],
                'ground_vehicle_sensitivity' => [
                    'infrared' => 100.0,
                    'cross_section' => 20.0,
                ],
                'piercing' => [
                    'infrared' => 200.0,
                    'cross_section' => 30.0,
                ],
            ];

            $output = Blade::render('<x-items.radar-card :radar="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Radar')
                ->toContain('Cooldown')
                ->toContain('2.50s')
                ->toContain('Sensitivity')
                ->toContain('Infrared')
                ->toContain('150.00')
                ->toContain('Ground Vehicle Sensitivity')
                ->toContain('Piercing');
        });

        it('handles empty radar data', function () {
            $data = [];

            $output = Blade::render('<x-items.radar-card :radar="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Radar')
                ->not->toContain('Sensitivity')
                ->not->toContain('Ground Vehicle Sensitivity');
        });
    });

    describe('Mining Laser Card', function () {
        it('renders mining laser with full data', function () {
            $data = [
                'laser_power' => [
                    'minimum' => 1000,
                    'maximum' => 5000,
                ],
                'module_slots' => 3,
                'throttle_lerp_speed' => 0.5,
                'throttle_minimum' => 0.1,
                'optimal_range' => 500.0,
                'maximum_range' => 1000.0,
                'extraction_throughput' => 250.5,
            ];

            $output = Blade::render('<x-items.mining-laser-card :miningLaser="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Mining Laser')
                ->toContain('Power & Control')
                ->toContain('Laser Power')
                ->toContain('1000 - 5000')
                ->toContain('Module Slots')
                ->toContain('3')
                ->toContain('Range')
                ->toContain('Optimal Range')
                ->toContain('500.00 m')
                ->toContain('Extraction')
                ->toContain('Extraction Throughput')
                ->toContain('250.50');
        });

        it('handles empty mining laser data', function () {
            $data = [];

            $output = Blade::render('<x-items.mining-laser-card :miningLaser="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Mining Laser')
                ->not->toContain('Power & Control')
                ->not->toContain('Range');
        });
    });

    describe('Mining Modifier Card', function () {
        it('renders mining modifier with full data', function () {
            $data = [
                'type' => 'Surge',
                'item_type' => 'Consumable',
                'charges' => 5,
                'duration' => 30.0,
                'power_modifier' => 1.5,
                'modifier_map' => [
                    'optimal_mass' => 20,
                    'shatter_mod' => 15,
                    'instability_mod' => -10,
                ],
            ];

            $output = Blade::render('<x-items.mining-modifier-card :miningModifier="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Mining Modifier Specifications')
                ->toContain('Type')
                ->toContain('Surge')
                ->toContain('Item Type')
                ->toContain('Consumable')
                ->toContain('Charges')
                ->toContain('5')
                ->toContain('Duration')
                ->toContain('30.00 s')
                ->toContain('Power Modifier')
                ->toContain('1.50x')
                ->toContain('Modifiers')
                ->toContain('Optimal Mass')
                ->toContain('20%');
        });

        it('shows unlimited charges when null', function () {
            $data = [
                'type' => 'Optical',
                'charges' => null,
            ];

            $output = Blade::render('<x-items.mining-modifier-card :miningModifier="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Charges')
                ->toContain('Unlimited');
        });

        it('handles empty mining modifier data', function () {
            $data = [];

            $output = Blade::render('<x-items.mining-modifier-card :miningModifier="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Mining Modifier Specifications')
                ->not->toContain('Type')
                ->not->toContain('Charges')
                ->not->toContain('Modifiers');
        });
    });

    describe('Jump Drive Card', function () {
        it('renders jump drive with full data', function () {
            $data = [
                'alignment_rate' => 10.5,
                'alignment_decay_rate' => 5.0,
                'tuning_rate' => 15.0,
                'fuel_usage_efficiency_multiplier' => 1.2,
                'travel_time_10gm' => [
                    'formatted' => '15m 30s',
                    's' => 930,
                ],
                'thermal_energy_draw' => [
                    'pre_ramp_up' => 50.0,
                    'ramp_up' => 100.0,
                    'in_flight' => 75.0,
                ],
            ];

            $output = Blade::render('<x-items.jump-drive-card :jumpDrive="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Jump Drive Specifications')
                ->toContain('Alignment Rate')
                ->toContain('10.50')
                ->toContain('Travel Time (10GM)')
                ->toContain('15m 30s')
                ->toContain('Thermal Energy Draw')
                ->toContain('Pre Ramp Up')
                ->toContain('50.00 heat units/s');
        });

        it('handles empty jump drive data', function () {
            $data = [];

            $output = Blade::render('<x-items.jump-drive-card :jumpDrive="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Jump Drive Specifications')
                ->not->toContain('Alignment Rate')
                ->not->toContain('Jump Modes');
        });
    });

    // ==================== COMPLEX COMPONENTS ====================

    describe('Quantum Drive Card', function () {
        it('renders quantum drive with full data', function () {
            $data = [
                'jump_range' => 100000000000,
                'disconnect_range' => 5000,
                'quantum_fuel_requirement' => 0.00000123,
                'fuel_rate' => 0.00000045,
                'fuel_consumption_scu_per_gm' => 0.00005,
                'fuel_efficiency' => 20000,
                'travel_time_10gm' => [
                    's' => 1800,
                ],
                'thermal_energy_draw' => [
                    'pre_ramp_up' => 50.0,
                    'ramp_up' => 100.0,
                    'in_flight' => 75.0,
                ],
                'modes' => [
                    [
                        'type' => 'precision',
                        'drive_speed' => 50000,
                    ],
                ],
            ];

            $output = Blade::render('<x-items.quantum-drive-card :quantumDrive="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Quantum Drive Specifications')
                ->toContain('Jump Range')
                ->toContain('1.00e+11 m')
                ->toContain('Disconnect Range')
                ->toContain('5,000 m')
                ->toContain('Quantum Fuel Requirement')
                ->toContain('Fuel Rate')
                ->toContain('Fuel Efficiency')
                ->toContain('20,000.00 GM / SCU')
                ->toContain('Thermal Energy Draw')
                ->toContain('Jump Modes')
                ->toContain('Precision');
        });

        it('handles empty quantum drive data', function () {
            $data = [];

            $output = Blade::render('<x-items.quantum-drive-card :quantumDrive="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Quantum Drive Specifications')
                ->not->toContain('Jump Range')
                ->not->toContain('Thermal Energy Draw');
        });
    });

    describe('Quantum Drive Jump Profile', function () {
        it('renders jump profile with all fields', function () {
            $data = [
                'drive_speed' => 200000,
                'cooldown_time' => 30.0,
                'stage_one_accel_rate' => 50000,
                'stage_two_accel_rate' => 100000,
                'engage_speed' => 1000,
                'interdiction_effect_time' => 15.0,
                'calibration_rate' => 100,
                'min_calibration_requirement' => 50,
                'max_calibration_requirement' => 100,
                'calibration_process_angle_limit' => 45.0,
                'calibration_warning_angle_limit' => 30.0,
                'calibration_delay_in_s' => 2.0,
                'spool_up_time' => 5.0,
            ];

            $output = Blade::render('<x-items.quantum-drive-jump-profile :profile="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Drive Speed')
                ->toContain('200,000 m/s')
                ->toContain('Cooldown Time')
                ->toContain('30.00 s')
                ->toContain('Stage One Acceleration Rate')
                ->toContain('50,000 m/s²')
                ->toContain('Calibration Rate')
                ->toContain('Calibration Process Angle Limit')
                ->toContain('45.00 deg');
        });

        it('handles empty jump profile', function () {
            $data = [];

            $output = Blade::render('<x-items.quantum-drive-jump-profile :profile="$data" />', ['data' => $data]);

            expect($output)
                ->not->toContain('Drive Speed')
                ->not->toContain('Cooldown Time');
        });
    });

    describe('Quantum Interdiction Generator Card', function () {
        it('renders with all sections', function () {
            $data = [
                'power_fractions' => [
                    'base' => 0.5,
                    'pulse' => 1.5,
                    'jammer' => 2.0,
                ],
                'jamming' => [
                    'range' => 5000,
                    'max_power_draw' => 100.0,
                    'green_zone_check_range' => 2500,
                ],
                'pulse' => [
                    'charge_time' => 10.0,
                    'discharge_time' => 5.0,
                    'radius' => 1000,
                ],
            ];

            $output = Blade::render('<x-items.quantum-interdiction-generator-card :quantumInterdictionGenerator="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Quantum Interdiction Generator Specifications')
                ->toContain('Power Fractions')
                ->toContain('Base')
                ->toContain('0.50')
                ->toContain('Jamming')
                ->toContain('Range')
                ->toContain('5,000 m')
                ->toContain('Pulse')
                ->toContain('Charge Time')
                ->toContain('10.00 s');
        });

        it('handles empty interdiction generator data', function () {
            $data = [];

            $output = Blade::render('<x-items.quantum-interdiction-generator-card :quantumInterdictionGenerator="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Quantum Interdiction Generator Specifications')
                ->not->toContain('Power Fractions')
                ->not->toContain('Jamming')
                ->not->toContain('Pulse');
        });
    });

    describe('Shield Card', function () {
        it('renders shield with full data', function () {
            $data = [
                'max_health' => 5000,
                'regen_rate' => 100,
                'regen_time' => 50.0,
                'decay_ratio' => 0.1,
                'electrical_charge_damage_resistance' => 0.5,
                'reserve_pool' => [
                    'regen_rate' => 50,
                    'regen_time' => 100.0,
                    'initial_health_ratio' => 0.2,
                    'max_health_ratio' => 0.5,
                ],
                'regen_delay' => [
                    'downed' => 10.0,
                    'damage' => 5.0,
                ],
                'absorption' => [
                    'energy' => ['min' => 0.5, 'max' => 0.8],
                    'physical' => ['min' => 0.3, 'max' => 0.6],
                ],
                'resistance' => [
                    'thermal' => ['min' => 0.2, 'max' => 0.5],
                ],
            ];

            $output = Blade::render('<x-items.shield-card :shield="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Shield Specifications')
                ->toContain('Max Health')
                ->toContain('5000')
                ->toContain('Regen Rate')
                ->toContain('100 / s')
                ->toContain('Reserve Pool')
                ->toContain('Regen Delay')
                ->toContain('Absorption')
                ->toContain('Energy')
                ->toContain('0.50')
                ->toContain('Resistance')
                ->toContain('Thermal');
        });

        it('handles empty shield data', function () {
            $data = [];

            $output = Blade::render('<x-items.shield-card :shield="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Shield Specifications')
                ->not->toContain('Max Health')
                ->not->toContain('Reserve Pool')
                ->not->toContain('Absorption')
                ->not->toContain('Resistance');
        });
    });

    describe('Turret Card', function () {
        it('renders turret with full axis data', function () {
            $data = [
                'rotation_style' => 'Gimbal',
                'mounts' => 2,
                'min_size' => 1,
                'max_size' => 2,
                'yaw_axis' => [
                    'slaved_only' => false,
                    'speed' => 90.0,
                    'time_to_full_speed' => 1.5,
                    'acceleration_decay' => 0.5,
                    'angle_limit_min' => -90.0,
                    'angle_limit_max' => 90.0,
                ],
                'pitch_axis' => [
                    'slaved_only' => true,
                    'speed' => 60.0,
                    'angle_limit_min' => -45.0,
                    'angle_limit_max' => 45.0,
                ],
            ];

            $output = Blade::render('<x-items.turret-card :turret="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Turret')
                ->toContain('Rotation Style')
                ->toContain('Gimbal')
                ->toContain('Yaw Axis')
                ->toContain('Slaved Only')
                ->toContain('No')
                ->toContain('Speed')
                ->toContain('90.00 deg/s')
                ->toContain('Angle Limit Min')
                ->toContain('-90.00 deg')
                ->toContain('Pitch Axis')
                ->toContain('Yes');
        });

        it('handles empty turret data', function () {
            $data = [];

            $output = Blade::render('<x-items.turret-card :turret="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Turret')
                ->not->toContain('Yaw Axis')
                ->not->toContain('Pitch Axis');
        });
    });

    describe('Personal Weapon Card', function () {
        it('renders personal weapon with damage breakdowns', function () {
            $data = [
                'class' => 'Assault Rifle',
                'type' => 'Energy',
                'capacity' => 30,
                'range' => 100,
                'fire_mode' => 'Automatic',
                'rpm' => 600,
                'pellets_per_shot' => 1,
                'damage' => [
                    'dps_total' => 180,
                    'alpha_total' => 18,
                    'maximum' => 540,
                    'dps' => [
                        'physical' => 60,
                        'energy' => 80,
                        'thermal' => 40,
                    ],
                    'alpha' => [
                        'physical' => 6,
                        'energy' => 8,
                        'thermal' => 4,
                    ],
                ],
                'spread' => [
                    'minimum' => 1,
                    'maximum' => 5,
                    'decay' => 2,
                ],
                'ads_spread' => [
                    'minimum' => 0.5,
                    'maximum' => 2,
                ],
                'charge' => [
                    'time' => 2,
                    'cooldown_time' => 1,
                ],
            ];

            $output = Blade::render('<x-items.personal-weapon-card :personalWeapon="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Personal Weapon')
                ->toContain('Assault Rifle Energy')
                ->toContain('Capacity')
                ->toContain('30 rounds')
                ->toContain('RPM')
                ->toContain('600 RPM')
                ->toContain('Fire Rate')
                ->toContain('Pellets per Shot')
                ->toContain('Damage Stats')
                ->toContain('DPS Total')
                ->toContain('180')
                ->toContain('DPS Breakdown')
                ->toContain('Physical')
                ->toContain('60')
                ->toContain('Alpha Breakdown')
                ->toContain('Spread')
                ->toContain('Hip-fire Spread')
                ->toContain('ADS Spread')
                ->toContain('Charge');
        });

        it('handles minimal personal weapon data', function () {
            $data = [
                'class' => 'Pistol',
                'type' => 'Ballistic',
            ];

            $output = Blade::render('<x-items.personal-weapon-card :personalWeapon="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Personal Weapon')
                ->toContain('Pistol Ballistic')
                ->not->toContain('Capacity')
                ->not->toContain('RPM')
                ->not->toContain('Fire Rate')
                ->not->toContain('Damage Stats');
        });
    });

    describe('Vehicle Weapon Card', function () {
        it('renders vehicle weapon with all sections', function () {
            $data = [
                'class' => 'Gimbal',
                'type' => 'Laser',
                'capacity' => 500,
                'range' => 2000,
                'rpm' => 200,
                'damage' => [
                    'sustained_60s' => 2000,
                    'burst' => 100,
                    'alpha_total' => 50,
                    'maximum' => 150,
                    'dps' => [
                        'energy' => 40,
                        'thermal' => 20,
                    ],
                    'alpha' => [
                        'energy' => 30,
                        'thermal' => 10,
                    ],
                ],
                'spread' => [
                    'minimum' => 0,
                    'maximum' => 2,
                    'decay' => 1,
                ],
                'heat' => [
                    'per_shot' => 5,
                    'cooling_delay' => 2,
                    'cooling_per_s' => 10,
                    'overheat_max_shots' => 20,
                ],
                'capacitor' => [
                    'max_ammo_load' => 100,
                    'regen_per_s' => 10,
                ],
                'charge' => [
                    'time' => 3,
                    'damage' => 50,
                    'fire_rate' => 150,
                ],
                'barrel_spin_time' => [
                    'up' => 2,
                    'down' => 1,
                ],
            ];

            $output = Blade::render('<x-items.vehicle-weapon-card :vehicleWeapon="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Vehicle Weapon')
                ->toContain('Gimbal Laser')
                ->toContain('Capacity')
                ->toContain('500 rounds')
                ->toContain('RPM')
                ->toContain('200 RPM')
                ->toContain('Damage Stats')
                ->toContain('Sustained 60s')
                ->toContain('2000')
                ->toContain('DPS Breakdown')
                ->toContain('Energy')
                ->toContain('40')
                ->toContain('Spread')
                ->toContain('Heat')
                ->toContain('Per Shot')
                ->toContain('5')
                ->toContain('Capacitor')
                ->toContain('Charge')
                ->toContain('Barrel Spin Time');
        });

        it('handles empty vehicle weapon data', function () {
            $data = [];

            $output = Blade::render('<x-items.vehicle-weapon-card :vehicleWeapon="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Vehicle Weapon')
                ->toContain('- -')
                ->not->toContain('Capacity')
                ->not->toContain('RPM')
                ->not->toContain('Damage Stats');
        });
    });

    describe('Weapon Attachment Card', function () {
        it('renders weapon attachment with all types', function () {
            $data = [
                'iron_sight' => [
                    'default_range' => 100,
                    'max_range' => 200,
                    'range_increment' => 25,
                    'zoom_scale' => 1.5,
                ],
                'laser_pointer' => [
                    'range' => 1000,
                    'color' => ['r' => 1, 'g' => 0, 'b' => 0],
                    'color_css' => 'rgb(255, 0, 0)',
                ],
                'flashlight' => [
                    'tactical' => [
                        'port_name' => 'Flashlight',
                        'light_radius' => 50,
                        'intensity' => 500,
                        'color_css' => 'rgb(255,255, 255)',
                    ],
                ],
                'magazine' => [
                    'initial_ammo_count' => 30,
                    'max_ammo_count' => 30,
                ],
                'compensator' => [
                    'attachment_point' => 'Barrel',
                    'type' => 'Muzzle Brake',
                    'recoil_reduction' => 0.3,
                ],
            ];

            $output = Blade::render('<x-items.weapon-attachment-card :weaponAttachment="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Weapon Attachment Specifications')
                ->toContain('Iron Sight')
                ->toContain('Default Range')
                ->toContain('100.00 m')
                ->toContain('Laser Pointer')
                ->toContain('Range')
                ->toContain('1,000.00 m')
                ->toContain('rgb(255, 0, 0)')
                ->toContain('Flashlight')
                ->toContain('Tactical')
                ->toContain('Light Radius')
                ->toContain('Magazine')
                ->toContain('Compensator')
                ->toContain('Barrel')
                ->toContain('Additional Fields');
        });

        it('handles empty weapon attachment data', function () {
            $data = [];

            $output = Blade::render('<x-items.weapon-attachment-card :weaponAttachment="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Weapon Attachment Specifications')
                ->not->toContain('Iron Sight')
                ->not->toContain('Laser Pointer')
                ->not->toContain('Flashlight');
        });
    });

    describe('Weapon Modifier Card', function () {
        it('renders weapon modifier with all sections', function () {
            $data = [
                'activate_on_attach' => true,
                'ignore_wear' => false,
                'base' => [
                    'muzzle_flash_multiplier' => 0.8,
                    'muzzle_flash_change' => -0.2,
                    'fire_rate_multiplier' => 1.2,
                    'fire_rate_change' => 0.2,
                    'damage_multiplier' => 1.1,
                    'damage_change' => 0.1,
                    'projectile_speed_multiplier' => 1.05,
                ],
                'recoil' => [
                    'multiplier' => 0.9,
                    'multiplier_change' => -0.1,
                    'decay_multiplier' => 1.1,
                ],
                'spread' => [
                    'min_multiplier' => 0.85,
                    'max_multiplier' => 0.9,
                    'decay_multiplier' => 1.15,
                ],
                'aim' => [
                    'zoom_scale' => 1.5,
                    'hide_weapon_in_ads' => true,
                    'fstop_multiplier' => 0.9,
                ],
                'regen' => [
                    'max_ammo_load_multiplier' => 1.5,
                    'max_regen_per_sec_multiplier' => 1.2,
                ],
                'salvage' => [
                    'salvage_speed_multiplier' => 1.3,
                    'radius_multiplier' => 1.2,
                ],
                'zeroing' => [
                    'default_range' => 100,
                    'max_range' => 500,
                ],
            ];

            $output = Blade::render('<x-items.weapon-modifier-card :weaponModifier="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Weapon Modifier Specifications')
                ->toContain('Activate On Attach')
                ->toContain('Yes')
                ->toContain('Ignore Wear')
                ->toContain('No')
                ->toContain('Base')
                ->toContain('Muzzle Flash Multiplier')
                ->toContain('0.80x')
                ->toContain('Muzzle Flash')
                ->toContain('-20.00%')
                ->toContain('Fire Rate')
                ->toContain('20.00%')
                ->toContain('Recoil')
                ->toContain('Spread')
                ->toContain('Aim')
                ->toContain('Regen')
                ->toContain('Salvage')
                ->toContain('Zeroing');
        });

        it('handles empty weapon modifier data', function () {
            $data = [];

            $output = Blade::render('<x-items.weapon-modifier-card :weaponModifier="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Weapon Modifier Specifications')
                ->not->toContain('Base')
                ->not->toContain('Recoil')
                ->not->toContain('Spread');
        });
    });

    describe('Tractor Beam Card', function () {
        it('renders tractor beam with all sections', function () {
            $data = [
                'force' => [
                    'min' => 100,
                    'max' => 1000,
                    'max_volume' => 10,
                    'volume_force_coefficient' => 0.5,
                ],
                'range' => [
                    'min' => 5,
                    'max' => 100,
                    'full_strength_distance' => 50,
                    'max_angle' => 45,
                    'hit_radius' => 2,
                ],
                'tether' => [
                    'tether_break_time' => 5,
                    'safe_range_value_factor' => 0.8,
                    'allow_scrolling_into_breaking_range' => false,
                ],
                'cargo_mode_override' => [
                    'min_force' => 200,
                    'max_force' => 2000,
                    'min_acceleration' => 5,
                    'max_acceleration' => 20,
                    'acceleration_factor' => 0.5,
                    'min_distance' => 10,
                    'max_distance' => 100,
                ],
                'towing' => [
                    'force' => 5000,
                    'max_acceleration' => 10,
                    'max_distance' => 200,
                    'qt_mass_limit' => 10000,
                ],
            ];

            $output = Blade::render('<x-items.tractor-beam-card :tractorBeam="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Tractor Beam')
                ->toContain('Force')
                ->toContain('Minimum Force')
                ->toContain('100N')
                ->toContain('Maximum Force')
                ->toContain('1,000N')
                ->toContain('Range')
                ->toContain('Minimum Range')
                ->toContain('5.00m')
                ->toContain('Tether')
                ->toContain('Allow Scrolling Into Breaking Range')
                ->toContain('No')
                ->toContain('Cargo Mode Override')
                ->toContain('Min Force')
                ->toContain('Min Acceleration')
                ->toContain('Towing')
                ->toContain('QT Mass Limit');
        });

        it('handles empty tractor beam data', function () {
            $data = [];

            $output = Blade::render('<x-items.tractor-beam-card :tractorBeam="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Tractor Beam')
                ->not->toContain('Force')
                ->not->toContain('Range')
                ->not->toContain('Tether');
        });
    });

    describe('Flight Controller Card', function () {
        it('renders flight controller with main speeds and angles', function () {
            $data = [
                'scm_speed' => 200,
                'boost_speed_forward' => 350,
                'boost_speed_backward' => 100,
                'max_speed' => 500,
                'pitch' => 60,
                'yaw' => 50,
                'roll' => 70,
                'pitch_boosted' => 90,
                'yaw_boosted' => 75,
                'roll_boosted' => 100,
            ];

            $output = Blade::render('<x-items.flight-controller-card :flightController="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Flight Controller')
                ->toContain('SCM Speed')
                ->toContain('200 m/s')
                ->toContain('Boost Speed Forward')
                ->toContain('350 m/s')
                ->toContain('Boost Speed Backward')
                ->toContain('100 m/s')
                ->toContain('Max Speed')
                ->toContain('500 m/s')
                ->toContain('Pitch')
                ->toContain('60 deg/s')
                ->toContain('Yaw')
                ->toContain('50 deg/s')
                ->toContain('Roll')
                ->toContain('70 deg/s')
                ->toContain('Pitch Boosted')
                ->toContain('90 deg/s');
        });

        it('renders flight controller with boost capacitor', function () {
            $data = [
                'boost_capacitor' => [
                    'capacity' => 100,
                    'threshold_ratio' => 0.2,
                    'idle_cost' => 0.1,
                    'linear_cost' => 0.5,
                    'angular_cost' => 0.3,
                    'regen_per_sec' => 10,
                    'regen_delay' => 2,
                    'regen_time' => 10,
                ],
            ];

            $output = Blade::render('<x-items.flight-controller-card :flightController="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Boost Capacitor')
                ->toContain('Capacity')
                ->toContain('100.00')
                ->toContain('Threshold Ratio')
                ->toContain('Idle Cost')
                ->toContain('Regen Per Sec')
                ->toContain('10.00')
                ->toContain('Regen Delay')
                ->toContain('2.00s');
        });

        it('handles empty flight controller data', function () {
            $data = [];

            $output = Blade::render('<x-items.flight-controller-card :flightController="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Flight Controller')
                ->not->toContain('SCM Speed')
                ->not->toContain('Boost Capacitor')
                ->not->toContain('Precision Mode');
        });
    });

    describe('Armor Card', function () {
        it('renders armor with full data', function () {
            $data = [
                'health' => 5000,
                'signal_multiplier' => [
                    'cross_section' => 10.5,
                    'cross_section_change' => -0.1,
                    'infrared' => 150.0,
                    'infrared_change' => 0.05,
                    'electromagnetic' => 25.0,
                    'electromagnetic_change' => 0.02,
                ],
                'damage_multiplier' => [
                    'physical' => 0.8,
                    'physical_change' => -0.05,
                    'energy' => 0.6,
                    'energy_change' => 0.03,
                    'distortion' => 0.4,
                    'distortion_change' => -0.02,
                    'thermal' => 0.7,
                    'thermal_change' => 0.04,
                    'biochemical' => 0.5,
                    'biochemical_change' => -0.01,
                    'stun' => 0.3,
                    'stun_change' => 0.01,
                ],
                'resistance_multiplier' => [
                    'physical' => 1.2,
                    'energy' => 1.5,
                    'distortion' => 1.0,
                    'thermal' => 1.1,
                    'biochemical' => 1.3,
                    'stun' => 0.9,
                ],
                'penetration_resistance' => [
                    'base' => 100,
                    'physical' => 120,
                    'energy' => 150,
                    'distortion' => 100,
                    'thermal' => 110,
                    'biochemical' => 130,
                    'stun' => 90,
                ],
            ];

            $output = Blade::render('<x-items.armor-card :armor="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Armor Specifications')
                ->toContain('Health')
                ->toContain('5,000')
                ->toContain('Signal Multipliers')
                ->toContain('Cross Section')
                ->toContain('10.50')
                ->toContain('Cross Section Change')
                ->toContain('-10%')
                ->toContain('Damage Multipliers')
                ->toContain('Physical')
                ->toContain('0.80')
                ->toContain('Physical Change')
                ->toContain('Resistance Multipliers')
                ->toContain('Penetration Resistance')
                ->toContain('Base')
                ->toContain('100.00');
        });

        it('handles empty armor data', function () {
            $data = [];

            $output = Blade::render('<x-items.armor-card :armor="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Armor Specifications')
                ->not->toContain('Health')
                ->not->toContain('Signal Multipliers')
                ->not->toContain('Damage Multipliers')
                ->not->toContain('Resistance Multipliers')
                ->not->toContain('Penetration Resistance');
        });
    });

    describe('Suit Armor Card', function () {
        it('renders suit armor with full data', function () {
            $data = [
                'slot' => 'Heavy',
                'damage_resistance_map' => [
                    'impact' => 0.8,
                    'impact_change' => -0.1,
                    'physical' => 0.7,
                    'physical_change' => -0.05,
                    'energy' => 0.6,
                    'energy_change' => 0.03,
                    'distortion' => 0.5,
                    'distortion_change' => 0.02,
                    'thermal' => 0.65,
                    'thermal_change' => -0.04,
                    'biochemical' => 0.55,
                    'biochemical_change' => 0.01,
                    'stun' => 0.4,
                    'stun_change' => -0.02,
                ],
                'signature' => [
                    'infrared' => 100.5,
                    'cross_section' => 15.2,
                    'electromagnetic' => 8.0,
                ],
                'radiation_resistance' => [
                    'maximum_radiation_capacity' => 250.0,
                    'radiation_dissipation_rate' => 5.5,
                ],
            ];

            $output = Blade::render('<x-items.suit-armor-card :suitArmor="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Suit Armor')
                ->toContain('Slot')
                ->toContain('Heavy')
                ->toContain('Damage Resistance Map')
                ->toContain('Impact')
                ->toContain('0.80')
                ->toContain('-10%')
                ->toContain('Physical')
                ->toContain('Energy')
                ->toContain('Distortion')
                ->toContain('Thermal')
                ->toContain('Biochemical')
                ->toContain('Stun')
                ->toContain('Signature')
                ->toContain('infrared')
                ->toContain('100.50')
                ->toContain('cross_section')
                ->toContain('15.20')
                ->toContain('Radiation Resistance')
                ->toContain('Maximum Radiation Capacity')
                ->toContain('250.00 REM')
                ->toContain('Radiation Dissipation Rate')
                ->toContain('5.50 REM/s');
        });

        it('handles empty suit armor data', function () {
            $data = [];

            $output = Blade::render('<x-items.suit-armor-card :suitArmor="$data" />', ['data' => $data]);

            expect($output)
                ->toContain('Suit Armor')
                ->not->toContain('Slot')
                ->not->toContain('Damage Resistance Map')
                ->not->toContain('Signature')
                ->not->toContain('Radiation Resistance');
        });
    });
});
