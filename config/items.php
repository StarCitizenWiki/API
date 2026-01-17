<?php

declare(strict_types=1);

if (! function_exists('suffix')) {
    function suffix(string $symbol, ?bool $precision = false, ?bool $space = true): array
    {
        return [
            'formatter' => 'money',
            'formatterParams' => [
                'symbol' => ($space ? ' ' : '').$symbol,
                'precision' => $precision,
                'symbolAfter' => true,
            ],
        ];
    }
}

if (! function_exists('numFormat')) {
    function numFormat(): array
    {
        return [
            'formatter' => 'money',
            'formatterParams' => [
                'precision' => false,
            ],
        ];
    }
}

return [
    /*
    |--------------------------------------------------------------------------
    | Shared Column Groups
    |--------------------------------------------------------------------------
    |
    | Define reusable column groups here that can be referenced across
    | multiple item types. These groups support nested columns and all
    | standard column configuration options.
    |
    | Usage in type_overrides:
    |   'shared' => ['groupName'],
    |   'shared_overrides' => [
    |       'groupName' => ['title' => 'Custom Title'],
    |   ],
    |
    */
    'shared_groups' => [
        'resourceNetwork.power_usage' => [
            'title' => 'Power Usage',
            'columns' => [
                [
                    'title' => 'Min',
                    'field' => 'resource_network.usage.power.minimum',
                    'sortField' => 'ResourceNetwork.Usage.Power.Minimum',
                ],
                [
                    'title' => 'Max',
                    'field' => 'resource_network.usage.power.maximum',
                    'sortField' => 'ResourceNetwork.Usage.Power.Maximum',
                ],
            ],
        ],
        'resourceNetwork.cooling_usage' => [
            'title' => 'Cooling Usage',
            'columns' => [
                [
                    'title' => 'Min',
                    'field' => 'resource_network.usage.coolant.minimum',
                    'sortField' => 'ResourceNetwork.Usage.Coolant.Minimum',
                ],
                [
                    'title' => 'Max',
                    'field' => 'resource_network.usage.coolant.maximum',
                    'sortField' => 'ResourceNetwork.Usage.Coolant.Maximum',
                ],
            ],
        ],
        'resourceNetwork.repair' => [
            'title' => 'Repair',
            'columns' => [
                [
                    'title' => 'Count',
                    'field' => 'resource_network.repair.max_repair_count',
                    'headerSort' => false,
                ],
                [
                    'title' => 'Time',
                    'field' => 'resource_network.repair.time_to_repair',
                    ...suffix('s', space: false),
                    'headerSort' => false,
                ],
                [
                    'title' => 'Health Ratio',
                    'field' => 'resource_network.repair.health_ratio',
                    'formatter' => 'pct',
                    'headerSort' => false,
                ],
            ],
        ],
        'resourceNetwork.emission' => [
            'title' => 'Signature',
            'columns' => [
                [
                    'title' => 'EM',
                    'columns' => [
                        [
                            'title' => 'Min',
                            'field' => 'emission.em_min',
                            'sortField' => 'Emission.Em.Minimum',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'emission.em_max',
                            'sortField' => 'Emission.Em.Maximum',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Decay',
                            'field' => 'emission.em_decay',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                    ],
                ],
                [
                    'title' => 'IR Start',
                    'field' => 'emission.ir',
                    'sortField' => 'Emission.Ir',
                    ...numFormat(),
                ],
            ],
        ],

        'durability' => [
            'title' => 'Durability',
            'columns' => [
                [
                    'title' => 'Health',
                    'field' => 'durability.health',
                    'sortField' => 'Durability.Health',
                    ...suffix('HP'),
                ],
                [
                    'title' => 'Shutdown Dmg',
                    'field' => 'distortion.maximum',
                    'sortField' => 'Distortion.Maximum',
                    ...numFormat(),
                ],
                [
                    'title' => 'Shutdown Time',
                    'field' => 'distortion.shutdown_time',
                    'sortField' => 'Distortion.ShutdownTime',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Decay Rate',
                    'field' => 'distortion.decay_rate',
                    'sortField' => 'Distortion.DecayRate',
                    ...numFormat(),
                ],
                [
                    'title' => 'Decay Delay',
                    'field' => 'distortion.decay_delay',
                    'sortField' => 'Distortion.DecayDelay',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Resistance Multiplier',
                    'columns' => [
                        [
                            'title' => 'Physical',
                            'field' => 'durability.resistance.physical',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'durability.resistance.energy',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'durability.resistance.distortion',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Thermal',
                            'field' => 'durability.resistance.thermal',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Biochemical',
                            'field' => 'durability.resistance.biochemical',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Stun',
                            'field' => 'durability.resistance.stun',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                    ],
                ],
            ],
        ],
        'temperature' => [
            'title' => 'Temperature °C',
            'columns' => [
                [
                    'title' => 'Cooling Threshold',
                    'field' => 'temperature.cooling_threshold',
                    ...suffix('°C'),
                    'headerSort' => false,
                ],
                [
                    'title' => 'IR Threshold',
                    'field' => 'temperature.ir_threshold',
                    ...suffix('°C'),
                    'headerSort' => false,
                ],
                [
                    'title' => 'Overheat',
                    'field' => 'temperature.overheat_temperature',
                    ...suffix('°C'),
                    'headerSort' => false,
                ],
                [
                    'title' => 'Max',
                    'field' => 'temperature.max_temperature',
                    ...suffix('°C'),
                    'headerSort' => false,
                ],
                [
                    'title' => 'Recovery',
                    'field' => 'temperature.recovery_temperature',
                    ...suffix('°C'),
                    'headerSort' => false,
                ],
            ],
        ],
        'occupancy' => [
            'title' => 'Occupancy',
            'columns' => [
                [
                    'title' => 'Mass',
                    'field' => 'mass',
                    'sortField' => 'Mass',
                    ...suffix('kg'),
                ],
                [
                    'title' => 'Weight',
                    'field' => 'dimension.volume_converted',
                    'sortField' => 'InventoryOccupancy.Volume.SCUConverted',
                    'formatter' => 'volumeWithUnit',
                    'formatterParams' => [
                        'unitField' => 'dimension.volume_converted_unit',
                    ],
                ],
            ],
        ],
        'inventory' => [
            'title' => 'Inventory',
            'field' => 'inventory.scu_converted',
            'sortField' => 'InventoryContainer.SCU',
            'formatter' => 'volumeWithUnit',
            'formatterParams' => [
                'unitField' => 'inventory.unit',
            ],
        ],
    ],

    'title' => [
        'default' => 'Items',
        'formatter' => '%s Items',
    ],
    'table' => [
        'columns' => [
            [
                'title' => 'Name',
                'field' => 'name',
                'headerSort' => true,
                'headerFilter' => 'input',
                'minWidth' => 220,
                'frozen' => true,
                'formatter' => 'link',
                'formatterParams' => [
                    'labelField' => 'name',
                    'target' => 'blank',
                    'urlField' => 'web_url',
                ],
            ],
            [
                'title' => 'Size',
                'field' => 'size',
                'sorter' => 'number',
                'sortField' => 'size',
                'headerSort' => true,
                'headerFilter' => 'list',
                'hozAlign' => 'right',
                'width' => 110,
                'frozen' => true,
            ],
            [
                'title' => 'Class Name',
                'field' => 'class_name',
                'sortField' => 'class_name',
                'headerSort' => true,
                'headerFilter' => 'input',
                'minWidth' => 220,
            ],
            [
                'title' => 'Manufacturer',
                'field' => 'manufacturer.name',
                'sortField' => 'manufacturer.name',
                'headerSort' => true,
                'headerFilter' => 'list',
                'minWidth' => 200,
            ],
            [
                'title' => 'Type',
                'field' => 'type',
                'sortField' => 'type',
                'headerSort' => true,
                'headerFilter' => 'list',
                'minWidth' => 200,
                'formatter' => 'link',
                'formatterParams' => [
                    'labelField' => 'type',
                    'urlField' => 'type_web_url',
                ],
            ],
            [
                'title' => 'Sub Type',
                'field' => 'sub_type',
                'sortField' => 'sub_type',
                'headerSort' => true,
                'headerFilter' => 'list',
                'minWidth' => 200,
            ],
            [
                'title' => 'Classification',
                'field' => 'classification',
                'sortField' => 'classification',
                'headerSort' => true,
                'headerFilter' => 'list',
                'minWidth' => 220,
            ],
            [
                'title' => 'Grade',
                'field' => 'grade',
                'sortField' => 'grade',
                'headerSort' => true,
                'headerFilter' => 'list',
                'minWidth' => 120,
            ],
            [
                'title' => 'Class',
                'field' => 'class',
                'sortField' => 'class',
                'headerSort' => true,
                'headerFilter' => 'list',
                'minWidth' => 140,
            ],
            [
                'title' => 'API Url',
                'field' => 'uuid',
                'formatter' => 'link',
                'formatterParams' => [
                    'label' => 'View',
                    'target' => 'blank',
                    'urlField' => 'link',
                ],
                'headerSort' => false,
                'hozAlign' => 'right',
                'width' => 100,
            ],
        ],
        'header_filter_options_map' => [
            'manufacturer.name' => 'manufacturer',
            'type' => 'type',
            'sub_type' => 'sub_type',
            'classification' => 'classification',
            'size' => 'size',
            'grade' => 'grade',
            'class' => 'class',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Type-Specific Overrides
    |--------------------------------------------------------------------------
    |
    | Customize table configuration for specific item types. Available options:
    |
    | - 'title': Custom page title
    | - 'shared': Array of shared group keys to include
    | - 'shared_insert_at': Position to insert shared groups (optional)
    |     * Positive: Insert at index (0-based)
    |     * Negative: Count from end (-1 = before last column)
    |     * Null/Omitted: Append at end (default)
    | - 'shared_overrides': Customize shared groups (e.g., change title)
    | - 'add_columns': Array of additional columns
    | - 'add_columns_insert_at': Position to insert additional columns (optional)
    |     * Same position rules as shared_insert_at
    | - 'remove_fields': Array of field names to remove
    | - 'header_filter_options_map': Add/override filter mappings
    |
    | Note: View button column automatically stays at end regardless of insertions.
    |
    */
    'type_overrides' => [
        'Armor' => [
            'title' => 'Vehicle Armor',
            'remove_fields' => ['classification', 'grade', 'class'],
            'shared' => ['durability', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Signals',
                    'columns' => [
                        [
                            'title' => 'Cross Section',
                            'field' => 'armor.signal_multiplier.cross_section_change',
                            'sortField' => 'Armor.SignalMultipliers.CrossSection',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Infrared',
                            'field' => 'armor.signal_multiplier.infrared_change',
                            'sortField' => 'Armor.SignalMultipliers.Infrared',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Electromagnetic',
                            'field' => 'armor.signal_multiplier.electromagnetic_change',
                            'sortField' => 'Armor.SignalMultipliers.Electromagnetic',
                            'formatter' => 'pctDelta',
                        ],
                    ],
                ],
                [
                    'title' => 'Damage',
                    'columns' => [
                        [
                            'title' => 'Physical',
                            'field' => 'armor.damage_multiplier.physical_change',
                            'formatter' => 'pctDelta',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'armor.damage_multiplier.energy_change',
                            'formatter' => 'pctDelta',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'armor.damage_multiplier.distortion_change',
                            'formatter' => 'pctDelta',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Thermal',
                            'field' => 'armor.damage_multiplier.thermal_change',
                            'formatter' => 'pctDelta',
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Penetration Resistance',
                    'columns' => [
                        [
                            'title' => 'Base',
                            'field' => 'armor.penetration_resistance.base',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Physical',
                            'field' => 'armor.penetration_resistance.physical',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'armor.penetration_resistance.energy',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'armor.penetration_resistance.distortion',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Thermal',
                            'field' => 'armor.penetration_resistance.thermal',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Stun',
                            'field' => 'armor.penetration_resistance.stun',
                            'formatter' => 'pct',
                            'headerSort' => false,
                        ],
                    ],
                ],
            ],
        ],
        'Bomb' => [
            'title' => 'Bombs',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['durability', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Damages',
                    'columns' => [
                        [
                            'title' => 'Total',
                            'field' => 'bomb.damage_total',
                            'sortField' => 'Bomb.DamageTotal',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Physical',
                            'field' => 'bomb.damage_map.physical',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'bomb.damage_map.energy',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'bomb.damage_map.distortion',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Thermal',
                            'field' => 'bomb.damage_map.thermal',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                    ],
                ],
                [
                    'title' => 'Explosion',
                    'columns' => [
                        [
                            'title' => 'Radius',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'bomb.explosion.radius_min',
                                    'sortField' => 'Bomb.ExplosionMinRadius',
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'bomb.explosion.radius_max',
                                    'sortField' => 'Bomb.ExplosionMaxRadius',
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Safety Distance',
                            'field' => 'bomb.explosion.safety_distance',
                            'headerSort' => false,
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Proximity',
                            'field' => 'bomb.explosion.proximity',
                            'headerSort' => false,
                            ...suffix('m'),
                        ],
                    ],
                ],
                [
                    'title' => 'Delays',
                    'columns' => [
                        [
                            'title' => 'Arm',
                            'field' => 'bomb.delays.arm_time',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Ignite',
                            'field' => 'bomb.delays.ignite_time',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Collision Delay',
                            'field' => 'bomb.delays.collision_delay_time',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
            ],
        ],
        'Cooler' => [
            'title' => 'Coolers',
            'remove_fields' => ['sub_type', 'classification'],
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'durability', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Coolant Generation',
                    'field' => 'resource_network.generation.coolant',
                    'sortField' => 'ResourceNetwork.Generation.Coolant',
                ],
            ],
        ],
        'EMP' => [
            'title' => 'EMP',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['durability', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Damage',
                    'field' => 'emp.distortion_damage',
                    'sortField' => 'Emp.DistortionDamage',
                    ...numFormat(),
                ],
                [
                    'title' => 'Radius',
                    'columns' => [
                        [
                            'title' => 'Min',
                            'field' => 'emp.min_emp_radius',
                            'sortField' => 'Emp.MinEmpRadius',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'emp.emp_radius',
                            'sortField' => 'Emp.EmpRadius',
                            ...suffix('m'),
                        ],
                    ],
                ],
                [
                    'title' => 'Delays',
                    'columns' => [
                        [
                            'title' => 'Charge',
                            'field' => 'emp.charge_duration',
                            'sortField' => 'Emp.ChargeTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Unleash',
                            'field' => 'emp.unleash_duration',
                            'sortField' => 'Emp.UnleashTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'emp.cooldown_duration',
                            'sortField' => 'Emp.CooldownTime',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
            ],
        ],
        'FlightController' => [
            'title' => 'Flight Blades',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'resourceNetwork.repair'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Speed',
                    'columns' => [
                        [
                            'title' => 'SCM',
                            'field' => 'flight_controller.scm_speed',
                            'sortField' => 'Ifcs.ScmSpeed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'NAV',
                            'field' => 'flight_controller.max_speed',
                            'sortField' => 'Ifcs.MaxSpeed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Boost Forward',
                            'field' => 'flight_controller.boost_speed_forward',
                            'sortField' => 'Ifcs.BoostSpeedForward',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Boost Backward',
                            'field' => 'flight_controller.boost_speed_backward',
                            'sortField' => 'Ifcs.BoostSpeedBackward',
                            ...suffix('m/s'),
                        ],
                    ],
                ],
                [
                    'title' => 'Rotation',
                    'columns' => [
                        [
                            'title' => 'Pitch',
                            'field' => 'flight_controller.pitch',
                            'sortField' => 'Ifcs.Pitch',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Yaw',
                            'field' => 'flight_controller.yaw',
                            'sortField' => 'Ifcs.Yaw',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Roll',
                            'field' => 'flight_controller.roll',
                            'sortField' => 'Ifcs.Roll',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Pitch (Boosted)',
                            'field' => 'flight_controller.pitch_boosted',
                            'sortField' => 'Ifcs.PitchBoosted',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Yaw (Boosted)',
                            'field' => 'flight_controller.yaw_boosted',
                            'sortField' => 'Ifcs.YawBoosted',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Roll (Boosted)',
                            'field' => 'flight_controller.roll_boosted',
                            'sortField' => 'Ifcs.RollBoosted',
                            ...suffix('º/s'),
                        ],
                    ],
                ],
                [
                    'title' => 'Thruster Decay',
                    'columns' => [
                        [
                            'title' => 'Linear',
                            'field' => 'flight_controller.thruster_decay.linear_accel',
                            'sortField' => 'Ifcs.LinearAccelDecay',
                        ],
                        [
                            'title' => 'Angular',
                            'field' => 'flight_controller.thruster_decay.angular_accel',
                            'sortField' => 'Ifcs.AngularAccelDecay',
                        ],
                    ],
                ],
                [
                    'title' => 'Multiplier',
                    'columns' => [
                        [
                            'title' => 'Lift',
                            'field' => 'flight_controller.multiplier.lift',
                            'sortField' => 'Ifcs.LiftMultiplier',
                        ],
                        [
                            'title' => 'Drag',
                            'field' => 'flight_controller.multiplier.drag',
                            'sortField' => 'Ifcs.DragMultiplier',
                        ],
                        [
                            'title' => 'SCM Drag',
                            'field' => 'flight_controller.multiplier.scm_max_drag',
                            'sortField' => 'Ifcs.ScmMaxDragMultiplier',
                        ],
                        [
                            'title' => 'Torque Imbalance',
                            'field' => 'flight_controller.multiplier.torque_imbalance',
                            'sortField' => 'Ifcs.TorqueImbalanceMultiplier',
                        ],
                        [
                            'title' => 'Precision Landing',
                            'field' => 'flight_controller.multiplier.precision_landing',
                            'sortField' => 'Ifcs.PrecisionLandingMultiplier',
                        ],
                    ],
                ],
                [
                    'title' => 'Boost',
                    'columns' => [
                        [
                            'title' => 'Segments',
                            'field' => 'flight_controller.boost_segments',
                        ],
                    ],
                ],
                [
                    'title' => 'Boost Activation',
                    'columns' => [
                        [
                            'title' => 'Pre-Delay',
                            'field' => 'flight_controller.boost_activation.pre_delay_time',
                            'sortField' => 'Ifcs.Afterburner.AfterburnerPreDelayTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Ramp-Up',
                            'field' => 'flight_controller.boost_activation.ramp_up_time',
                            'sortField' => 'Ifcs.Afterburner.AfterburnerRampUpTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Ramp-Down',
                            'field' => 'flight_controller.boost_activation.ramp_down_time',
                            'sortField' => 'Ifcs.Afterburner.AfterburnerRampDownTime',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Boost Multiplier',
                    'columns' => [
                        [
                            'title' => 'X',
                            'columns' => [
                                [
                                    'title' => '+',
                                    'field' => 'flight_controller.boost_multiplier.accel_x.positive',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => '-',
                                    'field' => 'flight_controller.boost_multiplier.accel_x.negative',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        [
                            'title' => 'Y',
                            'columns' => [
                                [
                                    'title' => '+',
                                    'field' => 'flight_controller.boost_multiplier.accel_y.positive',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => '-',
                                    'field' => 'flight_controller.boost_multiplier.accel_y.negative',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        [
                            'title' => 'Z',
                            'columns' => [
                                [
                                    'title' => '+',
                                    'field' => 'flight_controller.boost_multiplier.accel_z.positive',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => '-',
                                    'field' => 'flight_controller.boost_multiplier.accel_z.negative',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        [
                            'title' => 'Pitch',
                            'field' => 'flight_controller.boost_multiplier.pitch',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Yaw',
                            'field' => 'flight_controller.boost_multiplier.yaw',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Roll',
                            'field' => 'flight_controller.boost_multiplier.roll',
                            'headerSort' => false,
                        ],

                        [
                            'title' => 'Pitch Accel.',
                            'field' => 'flight_controller.boost_multiplier.pitch_accel',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Yaw Accel.',
                            'field' => 'flight_controller.boost_multiplier.yaw_accel',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Roll Accel.',
                            'field' => 'flight_controller.boost_multiplier.roll_accel',
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Boost Capacitor',
                    'columns' => [
                        [
                            'title' => 'Capacity',
                            'field' => 'flight_controller.boost_capacitor.capacity',
                            'sortField' => 'Ifcs.Afterburner.CapacitorMax',
                        ],
                        [
                            'title' => 'Threshold Ratio',
                            'field' => 'flight_controller.boost_capacitor.threshold_ratio',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Idle Cost',
                            'field' => 'flight_controller.boost_capacitor.idle_cost',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Linear Cost',
                            'field' => 'flight_controller.boost_capacitor.linear_cost',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Angular Cost',
                            'field' => 'flight_controller.boost_capacitor.angular_cost',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Regen Delay',
                            'field' => 'flight_controller.boost_capacitor.regen_delay',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Regen per Sec',
                            'field' => 'flight_controller.boost_capacitor.regen_per_sec',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Regen Time',
                            'field' => 'flight_controller.boost_capacitor.regen_time',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Hover',
                    'columns' => [
                        [
                            'title' => 'Max Speed',
                            'field' => 'flight_controller.gravlev.max_speed',
                            'sortField' => 'Ifcs.Gravlev.HoverMaxSpeed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Turn Friction',
                            'field' => 'flight_controller.gravlev.turn_friction',
                            'sortField' => 'Ifcs.Gravlev.TurnFriction',
                        ],
                        [
                            'title' => 'Air Controller Multiplier',
                            'field' => 'flight_controller.gravlev.air_controller_multiplier',
                            'sortField' => 'Ifcs.Gravlev.AirControllerMultiplier',
                        ],
                        [
                            'title' => 'Anti-Fall Multiplier',
                            'field' => 'flight_controller.gravlev.anti_fall_multiplier',
                            'sortField' => 'Ifcs.Gravlev.AntiFallMultiplier',
                        ],
                        [
                            'title' => 'Lateral Strafe Multiplier',
                            'field' => 'flight_controller.gravlev.lateral_strafe_multiplier',
                            'sortField' => 'Ifcs.Gravlev.LateralStrafeMultiplier',
                        ],
                    ],
                ],
            ],
        ],
        'JumpDrive' => [
            'title' => 'Jump Drives',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['durability', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Alignment Rate',
                    'field' => 'jump_drive.alignment_rate',
                    'sortField' => 'JumpDrive.AlignmentRate',
                ],
                [
                    'title' => 'Alignment Decay Rate',
                    'field' => 'jump_drive.alignment_decay_rate',
                    'sortField' => 'JumpDrive.AlignmentDecayRate',
                ],
                [
                    'title' => 'Tuning Rate',
                    'field' => 'jump_drive.tuning_rate',
                    'sortField' => 'JumpDrive.TuningRate',
                ],
                [
                    'title' => 'Tuning Decay Rate',
                    'field' => 'jump_drive.tuning_decay_rate',
                    'sortField' => 'JumpDrive.TuningDecayRate',
                ],
                [
                    'title' => 'Fuel Usage Efficiency Multiplier',
                    'field' => 'jump_drive.fuel_usage_efficiency_multiplier',
                    'sortField' => 'JumpDrive.FuelUsageEfficiencyMultiplier',
                ],
            ],
        ],
        'Missile' => [
            'title' => 'Missiles & Torpedoes',
            'remove_fields' => ['classification', 'grade', 'class'],
            'shared' => ['occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Tracking',
                    'columns' => [
                        [
                            'title' => 'Signal',
                            'field' => 'missile.signal_type',
                            'sortField' => 'Missile.Targeting.TrackingSignalType',
                        ],
                        [
                            'title' => 'Min',
                            'field' => 'missile.tracking_signal_min',
                            'sortField' => 'Missile.Targeting.SignalResilienceMin',
                        ],
                    ],
                ],
                [
                    'title' => 'Damages',
                    'columns' => [
                        [
                            'title' => 'Total',
                            'field' => 'missile.damage_total',
                            'sortField' => 'Missile.DamageTotal',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Physical',
                            'field' => 'missile.damage_map.physical',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'missile.damage_map.energy',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'missile.damage_map.distortion',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Thermal',
                            'field' => 'missile.damage_map.thermal',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                    ],
                ],
                [
                    'title' => 'Explosion',
                    'columns' => [
                        [
                            'title' => 'Radius',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'missile.explosion.radius_min',
                                    'sortField' => 'Missile.ExplosionRadius.Minimum',
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'missile.explosion.radius_max',
                                    'sortField' => 'Missile.ExplosionRadius.Maximum',
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Safety Distance',
                            'field' => 'missile.explosion.safety_distance',
                            'headerSort' => false,
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Proximity',
                            'field' => 'missile.explosion.proximity',
                            'headerSort' => false,
                            ...suffix('m'),
                        ],
                    ],
                ],
                [
                    'title' => 'Delays',
                    'columns' => [
                        [
                            'title' => 'Arm',
                            'field' => 'missile.delays.arm_time',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Lock',
                            'field' => 'missile.delays.lock_time',
                            'sortField' => 'Missile.Targeting.LockTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Ignite',
                            'field' => 'missile.delays.ignite_time',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Collision Delay',
                            'field' => 'missile.delays.collision_delay_time',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Target Lock',
                    'columns' => [
                        [
                            'title' => 'Acquisition',
                            'field' => 'missile.target_lock.signal_resilience_max',
                            'sortField' => 'Missile.Targeting.SignalResilienceMax',
                        ],
                        [
                            'title' => 'Range',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'missile.target_lock.range_min',
                                    'headerSort' => false,
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'missile.target_lock.range_max',
                                    'headerSort' => false,
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Angle',
                            'field' => 'missile.target_lock.angle',
                            'headerSort' => false,
                            ...suffix('°', space: false),
                        ],
                        [
                            'title' => 'Fire Without Lock',
                            'field' => 'missile.target_lock.allow_dumb_firing',
                            'sortField' => 'Missile.Targeting.AllowDumbFiring',
                        ],
                    ],
                ],
                [
                    'title' => 'Flight Characteristics',
                    'columns' => [
                        [
                            'title' => 'Speed',
                            'field' => 'missile.flight.speed',
                            'sortField' => 'Missile.GCS.LinearSpeed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Range',
                            'field' => 'missile.flight.range',
                            'sortField' => 'Missile.Range',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max Lifetime',
                            'field' => 'missile.flight.max_lifetime',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Boost Duration',
                            'field' => 'missile.flight.boost_phase_duration',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Terminal Phase Time',
                            'field' => 'missile.flight.terminal_phase_engagement_time',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Terminal Phase Angle',
                            'field' => 'missile.flight.terminal_phase_engagement_angle',
                            'headerSort' => false,
                            ...suffix('°', space: false),
                        ],
                        [
                            'title' => 'Boost Speed',
                            'field' => 'missile.flight.boost_speed',
                            'headerSort' => false,
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Intercept Speed',
                            'field' => 'missile.flight.intercept_speed',
                            'headerSort' => false,
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Terminal Speed',
                            'field' => 'missile.flight.terminal_speed',
                            'headerSort' => false,
                            ...suffix('m/s'),
                        ],
                    ],
                ],
            ],
        ],
        'MissileLauncher' => [
            'title' => 'Missile Racks',
            'remove_fields' => ['classification', 'grade', 'class'],
            'shared' => ['resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Capacity',
                    'field' => 'missile_rack.missile_count',
                    'sortField' => 'MissileRack.MissileCount',
                ],
                [
                    'title' => 'Size',
                    'field' => 'missile_rack.missile_size',
                    'sortField' => 'MissileRack.MissileSize',
                ],
            ],
        ],
        'Paints' => [
            'title' => 'Vehicle Paints',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Description',
                    'field' => 'description.en_EN',
                    'headerSort' => false,
                ],
            ],
        ],
        'PowerPlant' => [
            'title' => 'Power Plants',
            'remove_fields' => ['sub_type', 'classification'],
            'shared' => ['resourceNetwork.cooling_usage', 'durability', 'temperature', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Power Generation',
                    'field' => 'resource_network.generation.power',
                ],
                [
                    'title' => 'Signature',
                    'columns' => [
                        [
                            'title' => 'EM',
                            'columns' => [
                                [
                                    'title' => 'Per Segment',
                                    'field' => 'emission.em_per_segment',
                                    'sortField' => 'Emission.Em.PerSegment',
                                    ...numFormat(),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'emission.em_max',
                                    'sortField' => 'Emission.Em.Maximum',
                                    ...numFormat(),
                                ],
                                [
                                    'title' => 'Decay',
                                    'field' => 'emission.em_decay',
                                    'headerSort' => false,
                                    ...numFormat(),
                                ],
                            ],
                        ],
                        [
                            'title' => 'IR Start',
                            'field' => 'emission.ir',
                            'headerSort' => false,
                            ...numFormat(),
                        ],
                    ],
                ],
            ],
        ],
        'QuantumDrive' => [
            'title' => 'Quantum Drives',
            'remove_fields' => ['sub_type', 'classification'],
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'durability', 'temperature', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Travel',
                    'columns' => [
                        [
                            'title' => 'SCU per GM',
                            'field' => 'quantum_drive.fuel_consumption_scu_per_gm',
                            'sortField' => 'QuantumDrive.FuelConsumptionSCUPerGM',
                        ],
                        [
                            'title' => 'Efficiency',
                            'field' => 'quantum_drive.fuel_efficiency',
                            'sortField' => 'QuantumDrive.FuelEfficiencyGMPerSCU',
                        ],
                        [
                            'title' => 'Time per 10GM',
                            'field' => 'quantum_drive.travel_time_10gm.formatted',
                            'sortField' => 'QuantumDrive.TravelTime10GMSeconds',
                        ],
                    ],
                ],
                [
                    'title' => 'Delays',
                    'columns' => [
                        [
                            'title' => 'Spool-Up',
                            'field' => 'quantum_drive.standard_jump.spool_up_time',
                            'sortField' => 'QuantumDrive.StandardJump.SpoolUpTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'quantum_drive.standard_jump.cooldown_time',
                            'sortField' => 'QuantumDrive.StandardJump.CooldownTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Interdiction',
                            'field' => 'quantum_drive.standard_jump.interdiction_effect_time',
                            'sortField' => 'QuantumDrive.StandardJump.InterdictionEffectTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Calibration',
                            'field' => 'quantum_drive.standard_jump.calibration_delay_in_seconds',
                            'sortField' => 'QuantumDrive.StandardJump.CalibrationDelayInSeconds',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Speed',
                    'columns' => [
                        [
                            'title' => 'Max',
                            'field' => 'quantum_drive.standard_jump.drive_speed',
                            'headerSort' => false,
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Stage 1 Accel.',
                            'field' => 'quantum_drive.standard_jump.stage_one_accel_rate',
                            'sortField' => 'QuantumDrive.StandardJump.StageOneAccelRate',
                            ...suffix('m/s/s'),
                        ],
                        [
                            'title' => 'Stage 2 Accel.',
                            'field' => 'quantum_drive.standard_jump.stage_two_accel_rate',
                            'sortField' => 'QuantumDrive.StandardJump.StageTwoAccelRate',
                            ...suffix('m/s/s'),
                        ],
                        [
                            'title' => 'Spline',
                            'field' => 'quantum_drive.spline_jump.drive_speed',
                            'sortField' => 'QuantumDrive.SplineJump.DriveSpeed',
                            ...suffix('m/s'),
                        ],
                    ],
                ],
            ],
        ],
        'QuantumInterdictionGenerator' => [
            'title' => 'Quantum Interdiction Generators',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'resourceNetwork.cooling_usage'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Radius',
                    'columns' => [
                        [
                            'title' => 'Jamming',
                            'field' => 'quantum_interdiction_generator.jammer_range',
                            'sortField' => 'QuantumInterdictionGenerator.JammingRange',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Interdiction',
                            'field' => 'quantum_interdiction_generator.interdiction_range',
                            'sortField' => 'QuantumInterdictionGenerator.InterdictionRange',
                            ...suffix('m'),
                        ],
                    ],
                ],
                [
                    'title' => 'Interdiction Delays',
                    'columns' => [
                        [
                            'title' => 'Charge',
                            'field' => 'quantum_interdiction_generator.charge_duration',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Activation',
                            'field' => 'quantum_interdiction_generator.activation_duration',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Disperse Charge',
                            'field' => 'quantum_interdiction_generator.disperse_charge_duration',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Discharge',
                            'field' => 'quantum_interdiction_generator.discharge_duration',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'quantum_interdiction_generator.cooldown_duration',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                    ],
                ],

            ],
        ],
        'Radar' => [
            'title' => 'Radar',
            'remove_fields' => ['classification'],
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'durability', 'temperature', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Cooldown',
                    'field' => 'cooldown',
                    'sortField' => 'Cooldown',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Sensitivity',
                    'columns' => [
                        [
                            'title' => 'IR',
                            'field' => 'radar.sensitivity.infrared',
                            'sortField' => 'Radar.Sensitivity.IR',
                        ],
                        [
                            'title' => 'CS',
                            'field' => 'radar.sensitivity.cross_section',
                            'sortField' => 'Radar.Sensitivity.CS',
                        ],
                        [
                            'title' => 'EM',
                            'field' => 'radar.sensitivity.electromagnetic',
                            'sortField' => 'Radar.Sensitivity.EM',
                        ],
                        [
                            'title' => 'RS',
                            'field' => 'radar.sensitivity.resource',
                            'sortField' => 'Radar.Sensitivity.RS',
                        ],
                        [
                            'title' => 'dB',
                            'field' => 'radar.sensitivity.db',
                            'sortField' => 'Radar.Sensitivity.dB',
                        ],
                    ],
                ],
                [
                    'title' => 'Ground Vehicle Sensitivity',
                    'columns' => [
                        [
                            'title' => 'IR',
                            'field' => 'radar.ground_vehicle_sensitivity.infrared',
                            'sortField' => 'Radar.GroundVehicleSensitivity.IR',
                        ],
                        [
                            'title' => 'CS',
                            'field' => 'radar.ground_vehicle_sensitivity.cross_section',
                            'sortField' => 'Radar.GroundVehicleSensitivity.CS',
                        ],
                        [
                            'title' => 'EM',
                            'field' => 'radar.ground_vehicle_sensitivity.electromagnetic',
                            'sortField' => 'Radar.GroundVehicleSensitivity.EM',
                        ],
                        [
                            'title' => 'RS',
                            'field' => 'radar.ground_vehicle_sensitivity.resource',
                            'sortField' => 'Radar.GroundVehicleSensitivity.RS',
                        ],
                        [
                            'title' => 'dB',
                            'field' => 'radar.ground_vehicle_sensitivity.db',
                            'sortField' => 'Radar.GroundVehicleSensitivity.dB',
                        ],
                    ],
                ],
                [
                    'title' => 'Piercing',
                    'columns' => [
                        [
                            'title' => 'IR',
                            'field' => 'radar.piercing.infrared',
                            'sortField' => 'Radar.Piercing.IR',
                        ],
                        [
                            'title' => 'CS',
                            'field' => 'radar.piercing.cross_section',
                            'sortField' => 'Radar.Piercing.CS',
                        ],
                        [
                            'title' => 'EM',
                            'field' => 'radar.piercing.electromagnetic',
                            'sortField' => 'Radar.Piercing.EM',
                        ],
                        [
                            'title' => 'RS',
                            'field' => 'radar.piercing.resource',
                            'sortField' => 'Radar.Piercing.RS',
                        ],
                        [
                            'title' => 'dB',
                            'field' => 'radar.piercing.db',
                            'sortField' => 'Radar.Piercing.dB',
                        ],
                    ],
                ],
            ],
        ],
        'SalvageModifier' => [
            'title' => 'Salvage Modules',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Salvage Speed',
                    'field' => 'salvage_modifier.salvage_speed_multiplier',
                    'sortField' => 'SalvageModifier.SalvageSpeedMultiplier',
                ],
                [
                    'title' => 'Radius',
                    'field' => 'salvage_modifier.radius_multiplier',
                    'sortField' => 'SalvageModifier.RadiusMultiplier',
                ],
                [
                    'title' => 'Extraction Efficiency',
                    'field' => 'salvage_modifier.extraction_efficiency',
                    'sortField' => 'SalvageModifier.ExtractionEfficiency',
                ],
            ],
        ],
        'SelfDestruct' => [
            'title' => 'Self Destructs',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Damage',
                    'field' => 'self_destruct.damage',
                    'sortField' => 'SelfDestruct.Damage',
                    ...numFormat(),
                ],
                [
                    'title' => 'Countdown',
                    'field' => 'self_destruct.countdown',
                    'sortField' => 'SelfDestruct.Countdown',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Radius',
                    'columns' => [
                        [
                            'title' => 'Min',
                            'field' => 'self_destruct.min_radius',
                            'sortField' => 'SelfDestruct.MinRadius',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'self_destruct.phys_radius',
                            'sortField' => 'SelfDestruct.PhysRadius',
                            ...suffix('m'),
                        ],
                    ],
                ],
            ],
        ],
        'Shield' => [
            'title' => 'Shields',
            'remove_fields' => ['sub_type', 'classification'],
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'durability', 'temperature', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Shield',
                    'columns' => [
                        [
                            'title' => 'Health',
                            'field' => 'shield.max_health',
                            'sortField' => 'Shield.MaxShieldHealth',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Regen Rate',
                            'field' => 'shield.regen_rate',
                            'sortField' => 'Shield.MaxShieldRegen',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Regen Time',
                            'field' => 'shield.regen_time',
                            'sortField' => 'Shield.RegenerationTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Damage Delay',
                            'field' => 'shield.regen_delay.damage',
                            'sortField' => 'Shield.DamagedDelay',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Downed Delay',
                            'field' => 'shield.regen_delay.downed',
                            'sortField' => 'Shield.DownedDelay',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Reserve Pool',
                    'columns' => [
                        [
                            'title' => 'Regen Rate',
                            'field' => 'shield.reserve_pool.regen_rate',
                            'sortField' => 'Shield.ReservePool.MaxShieldRegen',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Regen Time',
                            'field' => 'shield.reserve_pool.regen_time',
                            'sortField' => 'Shield.ReservePool.RegenerationTime',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Absorption',
                    'columns' => [
                        [
                            'title' => 'Physical',
                            'columns' => [
                                [
                                    'title' => 'Max',
                                    'field' => 'shield.absorption.physical.max',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.absorption.physical.min',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        [
                            'title' => 'Energy',
                            'columns' => [
                                [
                                    'title' => 'Max',
                                    'field' => 'shield.absorption.energy.max',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.absorption.energy.min',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        [
                            'title' => 'Distortion',
                            'columns' => [
                                [
                                    'title' => 'Max',
                                    'field' => 'shield.absorption.distortion.max',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.absorption.distortion.min',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        // [
                        //     'title' => 'Thermal',
                        //     'columns' => [
                        //         [
                        //             'title' => 'Max',
                        //             'field' => 'shield.absorption.thermal.max',
                        //             'formatter' => 'pct',
                        //         ],
                        //         [
                        //             'title' => 'Min',
                        //             'field' => 'shield.absorption.thermal.min',
                        //             'formatter' => 'pct',
                        //         ],
                        //     ],
                        // ],
                        // [
                        //     'title' => 'Biochemical',
                        //     'columns' => [
                        //         [
                        //             'title' => 'Max',
                        //             'field' => 'shield.absorption.biochemical.max',
                        //             'formatter' => 'pct',
                        //         ],
                        //         [
                        //             'title' => 'Min',
                        //             'field' => 'shield.absorption.biochemical.min',
                        //             'formatter' => 'pct',
                        //         ],
                        //     ],
                        // ],
                        // [
                        //     'title' => 'Stun',
                        //     'columns' => [
                        //         [
                        //             'title' => 'Max',
                        //             'field' => 'shield.absorption.stun.max',
                        //             'formatter' => 'pct',
                        //         ],
                        //         [
                        //             'title' => 'Min',
                        //             'field' => 'shield.absorption.stun.min',
                        //             'formatter' => 'pct',
                        //         ],
                        //     ],
                        // ],
                    ],
                ],
                [
                    'title' => 'Resistance',
                    'columns' => [
                        [
                            'title' => 'Physical',
                            'columns' => [
                                [
                                    'title' => 'Max',
                                    'field' => 'shield.resistance.physical.max',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.resistance.physical.min',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        [
                            'title' => 'Energy',
                            'columns' => [
                                [
                                    'title' => 'Max',
                                    'field' => 'shield.resistance.energy.max',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.resistance.energy.min',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        [
                            'title' => 'Distortion',
                            'columns' => [
                                [
                                    'title' => 'Max',
                                    'field' => 'shield.resistance.distortion.max',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.resistance.distortion.min',
                                    'formatter' => 'pct',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        // [
                        //     'title' => 'Thermal',
                        //     'columns' => [
                        //         [
                        //             'title' => 'Max',
                        //             'field' => 'shield.resistance.thermal.max',
                        //             'formatter' => 'pct',
                        //         ],
                        //         [
                        //             'title' => 'Min',
                        //             'field' => 'shield.resistance.thermal.min',
                        //             'formatter' => 'pct',
                        //         ],
                        //     ],
                        // ],
                        // [
                        //     'title' => 'Biochemical',
                        //     'columns' => [
                        //         [
                        //             'title' => 'Max',
                        //             'field' => 'shield.resistance.biochemical.max',
                        //             'formatter' => 'pct',
                        //         ],
                        //         [
                        //             'title' => 'Min',
                        //             'field' => 'shield.resistance.biochemical.min',
                        //             'formatter' => 'pct',
                        //         ],
                        //     ],
                        // ],
                        // [
                        //     'title' => 'Stun',
                        //     'columns' => [
                        //         [
                        //             'title' => 'Max',
                        //             'field' => 'shield.resistance.stun.max',
                        //             'formatter' => 'pct',
                        //         ],
                        //         [
                        //             'title' => 'Min',
                        //             'field' => 'shield.resistance.stun.min',
                        //             'formatter' => 'pct',
                        //         ],
                        //     ],
                        // ],
                    ],
                ],
            ],
        ],
        'ShieldController' => [
            'title' => 'Shield Controllers',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Face Type',
                    'field' => 'shield_controller.face_type',
                    'sortField' => 'ShieldController.FaceType',
                ],
                [
                    'title' => 'Max Reallocation',
                    'field' => 'shield_controller.max_reallocation',
                    'sortField' => 'ShieldController.MaxReallocation',
                ],
                [
                    'title' => 'Reconfiguration Cooldown',
                    'field' => 'shield_controller.reconfiguration_cooldown',
                    'sortField' => 'ShieldController.ReconfigurationCooldown',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Max Electrical Charge Dmg Rate',
                    'field' => 'shield_controller.max_electrical_charge_damage_rate',
                    'sortField' => 'ShieldController.MaxElectricalChargeDamageRate',
                ],
            ],
        ],
        'TractorBeam,TowingBeam' => [
            'title' => 'Tractor & Towing Beams',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'durability', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Tractor',
                    'columns' => [
                        [
                            'title' => 'Force',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'tractor_beam.force.min',
                                    'sortField' => 'TractorBeam.MinForce',
                                    ...suffix('N'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'tractor_beam.force.max',
                                    'sortField' => 'TractorBeam.MaxForce',
                                    ...suffix('N'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Range',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'tractor_beam.range.min',
                                    'sortField' => 'TractorBeam.MinDistance',
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'tractor_beam.range.max',
                                    'sortField' => 'TractorBeam.MaxDistance',
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Full Strength',
                            'field' => 'tractor_beam.range.full_strength_distance',
                            'sortField' => 'TractorBeam.FullStrengthDistance',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max Angle',
                            'field' => 'tractor_beam.range.max_angle',
                            'sortField' => 'TractorBeam.MaxAngle',
                            ...suffix('°'),
                        ],
                        [
                            'title' => 'Max Volume',
                            'field' => 'tractor_beam.force.max_volume',
                            'sortField' => 'TractorBeam.MaxVolume',
                            ...suffix('m³'),
                        ],
                    ],
                ],
                [
                    'title' => 'Towing',
                    'columns' => [
                        [
                            'title' => 'Force',
                            'field' => 'tractor_beam.towing.force',
                            'sortField' => 'TractorBeam.Towing.TowingForce',
                            ...suffix('N'),
                        ],
                        [
                            'title' => 'Max Acceleration',
                            'field' => 'tractor_beam.towing.max_acceleration',
                            'sortField' => 'TractorBeam.Towing.TowingMaxAcceleration',
                            ...suffix('m/s²'),
                        ],
                        [
                            'title' => 'Max Distance',
                            'field' => 'tractor_beam.towing.max_distance',
                            'sortField' => 'TractorBeam.Towing.TowingMaxDistance',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'QT Mass Limit',
                            'field' => 'tractor_beam.towing.qt_mass_limit',
                            'sortField' => 'TractorBeam.Towing.QuantumTowMassLimit',
                            ...suffix('kg'),
                        ],
                    ],
                ],
            ],
        ],
        'Turret' => [
            'title' => 'Turrets & Gimbals',
            'remove_fields' => ['classification', 'grade', 'class'],
            'shared' => ['durability', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Mounts',
                    'columns' => [
                        [
                            'title' => 'Count',
                            'field' => 'turret.mounts',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Size Min',
                            'field' => 'turret.min_size',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Size Max',
                            'field' => 'turret.max_size',
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Yaw Axis',
                    'columns' => [
                        // [
                        //     'title' => 'Slaved Only',
                        //     'field' => 'turret.yaw_axis.slaved_only',
                        // ],
                        [
                            'title' => 'Speed',
                            'field' => 'turret.yaw_axis.speed',
                            ...suffix('m/s'),
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Time to full Speed',
                            'field' => 'turret.yaw_axis.time_to_full_speed',
                            ...suffix('s', space: false),
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Pitch Axis',
                    'columns' => [
                        // [
                        //     'title' => 'Slaved Only',
                        //     'field' => 'turret.pitch_axis.slaved_only',
                        // ],
                        [
                            'title' => 'Speed',
                            'field' => 'turret.pitch_axis.speed',
                            ...suffix('m/s'),
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Time to full Speed',
                            'field' => 'turret.pitch_axis.time_to_full_speed',
                            ...suffix('s', space: false),
                            'headerSort' => false,
                        ],
                    ],
                ],
            ],
        ],
        // 'weapon' => [
        //     'title' => 'Weapons',
        //     'add_columns' => [],
        //     'remove_fields' => [],
        //     'header_filter_options_map' => [],
        // ],
        'WeaponDefensive' => [
            'title' => 'Countermeasures',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Type',
                    'field' => 'counter_measure.type',
                    'sortField' => 'WeaponDefensive.Type',
                ],
                [
                    'title' => 'Ammo',
                    'columns' => [
                        [
                            'title' => 'Capacity',
                            'field' => 'ammunition.capacity',
                            'sortField' => 'WeaponDefensive.Capacity',
                        ],
                        [
                            'title' => 'Speed',
                            'field' => 'ammunition.speed',
                            'sortField' => 'Ammunition.Speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Range',
                            'field' => 'ammunition.range',
                            'sortField' => 'Ammunition.Range',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Lifetime',
                            'field' => 'ammunition.lifetime',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Signature',
                    'columns' => [
                        [
                            'title' => 'IR',
                            'field' => 'counter_measure.signature.infrared',
                            'sortField' => 'WeaponDefensive.Signatures.Infrared.End',
                        ],
                        [
                            'title' => 'CS',
                            'field' => 'counter_measure.signature.cross_section',
                            'sortField' => 'WeaponDefensive.Signatures.CrossSection.End',
                        ],
                        [
                            'title' => 'EM',
                            'field' => 'counter_measure.signature.electromagnetic',
                            'sortField' => 'WeaponDefensive.Signatures.Electromagnetic.End',
                        ],
                        [
                            'title' => 'dB',
                            'field' => 'counter_measure.signature.decibel',
                            'sortField' => 'WeaponDefensive.Signatures.Decibel.End',
                        ],
                    ],
                ],
            ],
        ],
        'WeaponGun' => [
            'title' => 'Hardpoint Weapons',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'durability', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Damage',
                    'columns' => [
                        [
                            'title' => 'Sustained 60s',
                            'field' => 'vehicle_weapon.damage.sustained_60s',
                            'sortField' => 'Weapon.Damage.Sustained60s',
                        ],
                        [
                            'title' => 'Burst',
                            'field' => 'vehicle_weapon.damage.burst',
                            'sortField' => 'Weapon.Damage.Burst',
                        ],
                        [
                            'title' => 'Alpha',
                            'field' => 'vehicle_weapon.damage.alpha_total',
                            'sortField' => 'Weapon.Damage.AlphaTotal',
                        ],
                        [
                            'title' => 'Maximum',
                            'field' => 'vehicle_weapon.damage.maximum',
                            'sortField' => 'Weapon.Damage.Maximum',
                        ],
                    ],
                ],
                [
                    'title' => 'Alpha Damage',
                    'columns' => [
                        [
                            'title' => 'Physical',
                            'field' => 'vehicle_weapon.damage.alpha.physical',
                            'sortField' => 'Weapon.Damage.Alpha.Physical',
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'vehicle_weapon.damage.alpha.energy',
                            'sortField' => 'Weapon.Damage.Alpha.Energy',
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'vehicle_weapon.damage.alpha.distortion',
                            'sortField' => 'Weapon.Damage.Alpha.Distortion',
                        ],
                    ],
                ],
                [
                    'title' => 'Ammunition',
                    'columns' => [
                        [
                            'title' => 'Speed',
                            'field' => 'ammunition.speed',
                            'sortField' => 'Ammunition.Speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Lifetime',
                            'field' => 'ammunition.lifetime',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Range',
                            'field' => 'ammunition.range',
                            'sortField' => 'Ammunition.Range',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Capacity',
                            'field' => 'ammunition.capacity',
                            'sortField' => 'Ammunition.Capacity',
                        ],
                        [
                            'title' => 'Penetration',
                            'columns' => [
                                [
                                    'title' => 'Base Distance',
                                    'field' => 'ammunition.penetration.base_distance',
                                    'sortField' => 'Ammunition.Penetration.BasePenetrationDistance',
                                ],
                                [
                                    'title' => 'Near Radius',
                                    'field' => 'ammunition.penetration.near_radius',
                                    'sortField' => 'Ammunition.Penetration.NearRadius',
                                ],
                                [
                                    'title' => 'Far Radius',
                                    'field' => 'ammunition.penetration.far_radius',
                                    'sortField' => 'Ammunition.Penetration.FarRadius',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Explosion Radius',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'ammunition.explosion_radius.min',
                                    'sortField' => 'Ammunition.ExplosionRadius.Minimum',
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'ammunition.explosion_radius.max',
                                    'sortField' => 'Ammunition.ExplosionRadius.Maximum',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Weapon',
                    'columns' => [
                        [
                            'title' => 'RPM',
                            'field' => 'vehicle_weapon.rpm',
                            'sortField' => 'Weapon.RateOfFire',
                        ],
                        [
                            'title' => 'Spread',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'vehicle_weapon.spread.min',
                                    'sortField' => 'Weapon.Spread.Minimum',
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'vehicle_weapon.spread.max',
                                    'sortField' => 'Weapon.Spread.Maximum',
                                ],
                                [
                                    'title' => 'First Attack',
                                    'field' => 'vehicle_weapon.spread.first_attack',
                                    'sortField' => 'Weapon.Spread.FirstAttack',
                                ],
                                [
                                    'title' => 'Per Attack',
                                    'field' => 'vehicle_weapon.spread.per_attack',
                                    'sortField' => 'Weapon.Spread.Attack',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Heat',
                    'columns' => [
                        [
                            'title' => 'Per Shot',
                            'field' => 'vehicle_weapon.heat.per_shot',
                            'sortField' => 'Weapon.Heat.HeatPerShot',
                        ],
                        [
                            'title' => 'Cooling Delay',
                            'field' => 'vehicle_weapon.heat.cooling_delay',
                            'sortField' => 'Weapon.Heat.CoolingDelay',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooling/s',
                            'field' => 'vehicle_weapon.heat.cooling_per_second',
                            'sortField' => 'Weapon.Heat.CoolingPerSecond',
                        ],
                    ],
                ],
                [
                    'title' => 'Overheat',
                    'columns' => [
                        [
                            'title' => 'Max Shots',
                            'field' => 'vehicle_weapon.heat.overheat_max_shots',
                            'sortField' => 'Weapon.Heat.ShotsToOverheat',
                        ],
                        [
                            'title' => 'Max Time',
                            'field' => 'vehicle_weapon.heat.overheat_max_time',
                            'sortField' => 'Weapon.Heat.TimeToOverheat',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'vehicle_weapon.heat.overheat_cooldown',
                            'sortField' => 'Weapon.Heat.OverheatFixTime',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Capacitor',
                    'columns' => [
                        [
                            'title' => 'Max Ammo',
                            'field' => 'vehicle_weapon.capacitor.max_ammo_load',
                            'sortField' => 'Weapon.Capacitor.MaxAmmoLoad',
                        ],
                        [
                            'title' => 'Regen/s',
                            'field' => 'vehicle_weapon.capacitor.regen_per_second',
                            'sortField' => 'Weapon.Capacitor.MaxRegenPerSec',
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'vehicle_weapon.capacitor.cooldown',
                            'sortField' => 'Weapon.Capacitor.Cooldown',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Charge',
                    'columns' => [
                        [
                            'title' => 'Time',
                            'field' => 'vehicle_weapon.charge.time',
                            'sortField' => 'Weapon.Charge.ChargeTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Overcharge',
                            'field' => 'vehicle_weapon.charge.overcharge',
                            'sortField' => 'Weapon.Charge.OverchargeTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Overcharged',
                            'field' => 'vehicle_weapon.charge.overcharged',
                            'sortField' => 'Weapon.Charge.OverchargedTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'vehicle_weapon.charge.cooldown',
                            'sortField' => 'Weapon.Charge.Cooldown',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Damage Modifier',
                            'field' => 'vehicle_weapon.charge_modifier.damage',
                            'sortField' => 'Weapon.ChargeModifier.Damage',
                            ...suffix('x', space: false),
                        ],
                    ],
                ],
            ],
        ],
        'WeaponMining' => [
            'title' => 'Mining Lasers',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'durability', 'resourceNetwork.repair', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Module Slots',
                    'field' => 'mining_laser.module_slots',
                    'sortField' => 'MiningLaser.ModuleSlots',
                ],
                [
                    'title' => 'Throttle',
                    'columns' => [
                        [
                            'title' => 'Lerp Speed',
                            'field' => 'mining_laser.throttle_lerp_speed',
                            'sortField' => 'MiningLaser.ThrottleLerpSpeed',
                        ],
                        [
                            'title' => 'Min',
                            'field' => 'mining_laser.throttle_minimumpower',
                            'sortField' => 'MiningLaser.ThrottleMinimum',
                        ],
                    ],
                ],
                [
                    'title' => 'Laser Power',
                    'columns' => [
                        [
                            'title' => 'Min',
                            'field' => 'mining_laser.laser_power.min',
                            'sortField' => 'MiningLaser.MinPowerTransfer',
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'mining_laser.laser_power.max',
                            'sortField' => 'MiningLaser.PowerTransfer',
                        ],
                    ],
                ],
                [
                    'title' => 'Range',
                    'columns' => [
                        [
                            'title' => 'Optimal',
                            'field' => 'mining_laser.optimal_range',
                            'sortField' => 'MiningLaser.OptimalRange',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'mining_laser.maximum_range',
                            'sortField' => 'MiningLaser.MaximumRange',
                            ...suffix('m'),
                        ],
                    ],
                ],
                [
                    'title' => 'Modifier',
                    'columns' => [
                        [
                            'title' => 'Resistance',
                            'field' => 'mining_laser.modifier_map.resistance',
                            'sortField' => 'MiningLaser.Modifiers.Resistance',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Instability',
                            'field' => 'mining_laser.modifier_map.instability',
                            'sortField' => 'MiningLaser.Modifiers.Instability',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Optimal Charge Window',
                            'field' => 'mining_laser.modifier_map.optimal_charge_window_size',
                            'sortField' => 'MiningLaser.Modifiers.OptimalChargeWindow',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Optimal Rate',
                            'field' => 'mining_laser.modifier_map.optimal_charge_rate',
                            'sortField' => 'MiningLaser.Modifiers.OptimalChargeRate',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Inert Materials',
                            'field' => 'mining_laser.modifier_map.inert_materials',
                            'sortField' => 'MiningLaser.Modifiers.InertMaterials',
                            ...suffix('%', space: false),
                        ],
                    ],
                ],
            ],
        ],

        'WeaponPersonal' => [
            'title' => 'Personal Weapons',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Weapon',
                    'columns' => [
                        [
                            'title' => 'Class',
                            'field' => 'personal_weapon.class',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Type',
                            'field' => 'personal_weapon.type',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'RPM',
                            'field' => 'personal_weapon.rpm',
                            'sortField' => 'Weapon.RateOfFire',
                        ],
                    ],
                ],
                [
                    'title' => 'Damage',
                    'columns' => [
                        [
                            'title' => 'Burst',
                            'field' => 'personal_weapon.damage.dps_total',
                            'sortField' => 'Weapon.Damage.DpsTotal',
                        ],
                        [
                            'title' => 'Alpha',
                            'field' => 'personal_weapon.damage.alpha_total',
                            'sortField' => 'Weapon.Damage.AlphaTotal',
                        ],
                        [
                            'title' => 'Per Mag',
                            'field' => 'personal_weapon.damage.maximum',
                            'sortField' => 'Weapon.Damage.MaxPerMag',
                        ],
                    ],
                ],
                [
                    'title' => 'Alpha Damage',
                    'columns' => [
                        [
                            'title' => 'Physical',
                            'field' => 'personal_weapon.damage.alpha.physical',
                            'sortField' => 'Weapon.Damage.Alpha.Physical',
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'personal_weapon.damage.alpha.energy',
                            'sortField' => 'Weapon.Damage.Alpha.Energy',
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'personal_weapon.damage.alpha.distortion',
                            'sortField' => 'Weapon.Damage.Alpha.Distortion',
                        ],
                        [
                            'title' => 'Stun',
                            'field' => 'personal_weapon.damage.alpha.stun',
                            'sortField' => 'Weapon.Damage.Alpha.Stun',
                        ],
                    ],
                ],
                [
                    'title' => 'DPS',
                    'columns' => [
                        [
                            'title' => 'Physical',
                            'field' => 'personal_weapon.damage.dps.physical',
                            'sortField' => 'Weapon.Damage.Dps.Physical',
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'personal_weapon.damage.dps.energy',
                            'sortField' => 'Weapon.Damage.Dps.Energy',
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'personal_weapon.damage.dps.distortion',
                            'sortField' => 'Weapon.Damage.Dps.Distortion',
                        ],
                        [
                            'title' => 'Stun',
                            'field' => 'personal_weapon.damage.dps.stun',
                            'sortField' => 'Weapon.Damage.Dps.Stun',
                        ],
                    ],
                ],
                [
                    'title' => 'Ammunition',
                    'columns' => [
                        [
                            'title' => 'Speed',
                            'field' => 'ammunition.speed',
                            'sortField' => 'Ammunition.Speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Lifetime',
                            'field' => 'ammunition.lifetime',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Range',
                            'field' => 'ammunition.range',
                            'sortField' => 'Ammunition.Range',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Capacity',
                            'field' => 'ammunition.capacity',
                            'sortField' => 'Ammunition.Capacity',
                        ],
                        [
                            'title' => 'Pellets per Shot',
                            'field' => 'personal_weapon.pellets_per_shot',
                            'sortField' => 'Weapon.PelletsPerShot',
                        ],
                        [
                            'title' => 'Penetration',
                            'columns' => [
                                [
                                    'title' => 'Base Distance',
                                    'field' => 'ammunition.penetration.base_distance',
                                    'sortField' => 'Ammunition.Penetration.BasePenetrationDistance',
                                ],
                                [
                                    'title' => 'Near Radius',
                                    'field' => 'ammunition.penetration.near_radius',
                                    'sortField' => 'Ammunition.Penetration.NearRadius',
                                ],
                                [
                                    'title' => 'Far Radius',
                                    'field' => 'ammunition.penetration.far_radius',
                                    'sortField' => 'Ammunition.Penetration.FarRadius',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Explosion Radius',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'ammunition.explosion_radius.min',
                                    'sortField' => 'Ammunition.ExplosionRadius.Minimum',
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'ammunition.explosion_radius.max',
                                    'sortField' => 'Ammunition.ExplosionRadius.Maximum',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Damage Drop',
                    'columns' => [
                        [
                            'title' => 'Min Distance',
                            'field' => 'ammunition.damage_drop_min_distance.total',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Per Meter',
                            'field' => 'ammunition.damage_drop_per_meter.total',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Min Alpha',
                            'field' => 'ammunition.damage_drop_min_damage.total',
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Spread',
                    'columns' => [
                        [
                            'title' => 'Min',
                            'field' => 'personal_weapon.spread.min',
                            'sortField' => 'Weapon.Spread.Minimum',
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'personal_weapon.spread.max',
                            'sortField' => 'Weapon.Spread.Maximum',
                        ],
                        [
                            'title' => 'First Attack',
                            'field' => 'personal_weapon.spread.first_attack',
                            'sortField' => 'Weapon.Spread.FirstAttack',
                        ],
                        [
                            'title' => 'Per Attack',
                            'field' => 'personal_weapon.spread.per_attack',
                            'sortField' => 'Weapon.Spread.Attack',
                        ],
                    ],
                ],
                [
                    'title' => 'ADS Spread',
                    'columns' => [
                        [
                            'title' => 'Min',
                            'field' => 'personal_weapon.ads_spread.min',
                            'sortField' => 'Weapon.AdsSpread.Minimum',
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'personal_weapon.ads_spread.max',
                            'sortField' => 'Weapon.AdsSpread.Maximum',
                        ],
                        [
                            'title' => 'First Attack',
                            'field' => 'personal_weapon.ads_spread.first_attack',
                            'sortField' => 'Weapon.AdsSpread.FirstAttack',
                        ],
                        [
                            'title' => 'Per Attack',
                            'field' => 'personal_weapon.ads_spread.per_attack',
                            'sortField' => 'Weapon.AdsSpread.Attack',
                        ],
                    ],
                ],
                [
                    'title' => 'Charge Action',
                    'columns' => [
                        [
                            'title' => 'Charge Time',
                            'field' => 'personal_weapon.charge.time',
                            'sortField' => 'Weapon.Charge.ChargeTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Overcharge',
                            'field' => 'personal_weapon.charge.overcharge_time',
                            'sortField' => 'Weapon.Charge.OverchargeTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Overcharged',
                            'field' => 'personal_weapon.charge.overcharged_time',
                            'sortField' => 'Weapon.Charge.OverchargedTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'personal_weapon.charge.cooldown_time',
                            'sortField' => 'Weapon.Charge.CooldownTime',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Charge Modifier',
                    'columns' => [
                        [
                            'title' => 'Damage',
                            'field' => 'personal_weapon.charge_modifier.damage',
                            'sortField' => 'Weapon.ChargeModifier.Damage',
                            ...suffix('x', space: false),
                        ],
                        [
                            'title' => 'Fire Rate',
                            'field' => 'personal_weapon.charge_modifier.fire_rate',
                            'sortField' => 'Weapon.ChargeModifier.FireRate',
                            ...suffix('x', space: false),
                        ],
                        [
                            'title' => 'Ammo Speed',
                            'field' => 'personal_weapon.charge_modifier.ammo_speed',
                            'sortField' => 'Weapon.ChargeModifier.AmmoSpeed',
                            ...suffix('x', space: false),
                        ],
                    ],
                ],
            ],
        ],

        // Categories

        'clothes' => [
            'title' => 'Clothing',
            'remove_fields' => ['grade', 'class'],
            'shared' => ['inventory', 'occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Clothing',
                    'columns' => [
                        [
                            'title' => 'Slot',
                            'field' => 'clothing.slot',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Type',
                            'field' => 'clothing.type',
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Resistance',
                    'columns' => [
                        [
                            'title' => 'Temperature',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'clothing.temperature_resistance.min',
                                    'sortField' => 'Clothing.TemperatureResistance.Minimum',
                                    ...suffix('ºC'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'clothing.temperature_resistance.max',
                                    'sortField' => 'Clothing.TemperatureResistance.Maximum',
                                    ...suffix('ºC'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Radiation',
                            'columns' => [
                                [
                                    'title' => 'Capacity',
                                    'field' => 'clothing.radiation_resistance.maximum_radiation_capacity',
                                    'sortField' => 'Clothing.RadiationResistance.MaximumRadiationCapacity',
                                ],
                                [
                                    'title' => 'Scrub Rate',
                                    'field' => 'clothing.radiation_resistance.radiation_dissipation_rate',
                                    'sortField' => 'Clothing.RadiationResistance.RadiationDissipationRate',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'food' => [
            'title' => 'Food & Drinks',
            'remove_fields' => ['grade', 'class'],
            'shared' => ['occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Nutrition',
                    'columns' => [
                        [
                            'title' => 'Thirst',
                            'field' => 'food.nutrition.thirst',
                            'sortField' => 'Food.Nutrition.Thirst.Total',
                        ],
                        [
                            'title' => 'Hunger',
                            'field' => 'food.nutrition.hunger',
                            'sortField' => 'Food.Nutrition.Hunger.Total',
                        ],
                        [
                            'title' => 'Blood Drug Level',
                            'field' => 'food.nutrition.blood_drug_level',
                            'sortField' => 'Food.Nutrition.BloodDrugLevel.Total',
                        ],
                    ],
                ],
                [
                    'title' => 'Buffs',
                    'columns' => [
                        [
                            'title' => 'Hypertrophic',
                            'field' => 'food.buffs.hypertrophic',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Hypo Metabolic',
                            'field' => 'food.buffs.hypo_metabolic',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Hydrating',
                            'field' => 'food.buffs.hydrating',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cognitive Boost',
                            'field' => 'food.buffs.cognitive_boost',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Energizing',
                            'field' => 'food.buffs.energizing',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Immune',
                            'field' => 'food.buffs.immune_boost',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Debuffs',
                    'columns' => [
                        [
                            'title' => 'Cognitive Impair',
                            'field' => 'food.debuffs.cognitive_impair',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Dehydrating',
                            'field' => 'food.debuffs.dehydrating',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Hyper Metabolic',
                            'field' => 'food.debuffs.hyper_metabolic',
                            'headerSort' => false,
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
                [
                    'title' => 'Consumption',
                    'columns' => [
                        [
                            'title' => 'Volume',
                            'field' => 'food.consumption.volume',
                            'headerSort' => false,
                            ...suffix('µSCU'),
                        ],
                        [
                            'title' => 'One Shot',
                            'field' => 'food.consumption.one_shot_consume',
                            'headerSort' => false,
                            'formatter' => 'tickCross',
                        ],
                    ],
                ],
                [
                    'title' => 'Container',
                    'columns' => [
                        [
                            'title' => 'Type',
                            'field' => 'food.container.type',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Closed',
                            'field' => 'food.container.closed',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Closable',
                            'field' => 'food.container.can_be_reclosed',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Discard',
                            'field' => 'food.container.discard_when_consumed',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                    ],
                ],
            ],
        ],
        'fps-armor' => [
            'title' => 'FPS Armor',
            'remove_fields' => ['grade', 'class'],
            'shared' => ['inventory', 'occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Armor',
                    'columns' => [
                        [
                            'title' => 'Slot',
                            'field' => 'clothing.slot',
                            'headerSort' => false,
                        ],
                        // [
                        //     'title' => 'Type',
                        //     'field' => 'clothing.type',
                        // ],
                    ],
                ],

                [
                    'title' => 'Resistance',
                    'columns' => [
                        [
                            'title' => 'Temperature',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'clothing.temperature_resistance.min',
                                    'sortField' => 'Clothing.TemperatureResistance.Minimum',
                                    ...suffix('ºC'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'clothing.temperature_resistance.max',
                                    'sortField' => 'Clothing.TemperatureResistance.Maximum',
                                    ...suffix('ºC'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Radiation',
                            'columns' => [
                                [
                                    'title' => 'Capacity',
                                    'field' => 'clothing.radiation_resistance.maximum_radiation_capacity',
                                    'sortField' => 'Clothing.RadiationResistance.MaximumRadiationCapacity',
                                    ...suffix('REM'),
                                ],
                                [
                                    'title' => 'Scrub Rate',
                                    'field' => 'clothing.radiation_resistance.radiation_dissipation_rate',
                                    'sortField' => 'Clothing.RadiationResistance.RadiationDissipationRate',
                                    ...suffix('REM/s'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Damage',
                            'columns' => [
                                [
                                    'title' => 'Impact',
                                    'field' => 'clothing.damage_resistance_map.impact',
                                    'sortField' => 'SuitArmor.DamageResistance.Impact',
                                    'formatter' => 'pct',
                                ],
                                [
                                    'title' => 'Physical',
                                    'field' => 'clothing.damage_resistance_map.physical',
                                    'sortField' => 'SuitArmor.DamageResistance.Physical.Multiplier',
                                    'formatter' => 'pct',
                                ],
                                [
                                    'title' => 'Energy',
                                    'field' => 'clothing.damage_resistance_map.energy',
                                    'sortField' => 'SuitArmor.DamageResistance.Energy.Multiplier',
                                    'formatter' => 'pct',
                                ],
                                [
                                    'title' => 'Distortion',
                                    'field' => 'clothing.damage_resistance_map.distortion',
                                    'sortField' => 'SuitArmor.DamageResistance.Distortion.Multiplier',
                                    'formatter' => 'pct',
                                ],
                                [
                                    'title' => 'Thermal',
                                    'field' => 'clothing.damage_resistance_map.thermal',
                                    'sortField' => 'SuitArmor.DamageResistance.Thermal.Multiplier',
                                    'formatter' => 'pct',
                                ],
                                [
                                    'title' => 'Stun',
                                    'field' => 'clothing.damage_resistance_map.stun',
                                    'sortField' => 'SuitArmor.DamageResistance.Stun.Multiplier',
                                    'formatter' => 'pct',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Signature',
                    'columns' => [
                        [
                            'title' => 'EM',
                            'field' => 'clothing.signature.electromagnetic',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'IR',
                            'field' => 'clothing.signature.infrared',
                            'headerSort' => false,
                        ],
                    ],
                ],
            ],
        ],
        'medical' => [
            'title' => 'Medicine',
            'remove_fields' => ['grade', 'class'],
            'shared' => ['occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Combat Buffs',
                    'columns' => [
                        [
                            'title' => 'Stun Recovery',
                            'field' => 'medical.combat_buffs.stun_recovery',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Move Speed',
                            'field' => 'medical.combat_buffs.move_speed',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Weapon Sway',
                            'field' => 'medical.combat_buffs.weapon_sway',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'ADS Enter',
                            'field' => 'medical.combat_buffs.a_d_s_enter',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Impact Resistance',
                    'columns' => [
                        [
                            'title' => 'Knockdown',
                            'field' => 'medical.impact_resistances.knockdown',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Stagger',
                            'field' => 'medical.impact_resistances.stagger',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Twitch',
                            'field' => 'medical.impact_resistances.twitch',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Flinch',
                            'field' => 'medical.impact_resistances.flinch',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Consumption',
                    'columns' => [
                        [
                            'title' => 'Volume',
                            'field' => 'medical.consumption.volume',
                            ...suffix('µSCU'),
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'One Shot',
                            'field' => 'medical.consumption.one_shot_consume',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Container',
                    'columns' => [
                        [
                            'title' => 'Type',
                            'field' => 'medical.container.type',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Closed',
                            'field' => 'medical.container.closed',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Closable',
                            'field' => 'medical.container.can_be_reclosed',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                        [
                            'title' => 'Discard',
                            'field' => 'medical.container.discard_when_consumed',
                            'formatter' => 'tickCross',
                            'headerSort' => false,
                        ],
                    ],
                ],
            ],
        ],
        'mining-modifiers' => [
            'title' => 'Mining Modules & Gadgets',
            'remove_fields' => ['sub_type', 'classification', 'grade', 'class'],
            'shared' => ['durability', 'occupancy'],
            'shared_insert_at' => 2,

            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Type',
                    'field' => 'mining_modifier.item_type',
                    'headerSort' => false,
                ],
                [
                    'title' => 'Status',
                    'field' => 'mining_modifier.type',
                    'sortField' => 'MiningModule.Type',
                ],
                [
                    'title' => 'Charges',
                    'field' => 'mining_modifier.charges',
                    'sortField' => 'MiningModule.Charges',
                ],
                [
                    'title' => 'Duration',
                    'field' => 'mining_modifier.duration',
                    'sortField' => 'MiningModule.Lifetime',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Modifier (Mining/Extraction)',
                    'field' => 'mining_modifier.power_modifier',
                    'sortField' => 'MiningModule.Modifiers.DamageMultiplierChange',
                    'formatter' => 'pct',
                ],
                [
                    'title' => 'Modifier',
                    'columns' => [
                        [
                            'title' => 'Resistance',
                            'field' => 'mining_modifier.modifier_map.resistance',
                            'sortField' => 'MiningModule.Modifiers.Resistance',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Instability',
                            'field' => 'mining_modifier.modifier_map.instability',
                            'sortField' => 'MiningModule.Modifiers.Instability',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Optimal Charge Window',
                            'field' => 'mining_modifier.modifier_map.optimal_charge_window_size',
                            'sortField' => 'MiningModule.Modifiers.OptimalChargeWindow',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Optimal Rate',
                            'field' => 'mining_modifier.modifier_map.optimal_charge_rate',
                            'sortField' => 'MiningModule.Modifiers.OptimalChargeRate',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Shatter Damage',
                            'field' => 'mining_modifier.modifier_map.shatter_damage',
                            'sortField' => 'MiningModule.Modifiers.ShatterDamage',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Cluster Factor',
                            'field' => 'mining_modifier.modifier_map.cluster_factor',
                            'sortField' => 'MiningModule.Modifiers.ClusterFactor',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Overcharge Rate',
                            'field' => 'mining_modifier.modifier_map.overcharge_rate',
                            'sortField' => 'MiningModule.Modifiers.OverchargeRate',
                            ...suffix('%', space: false),
                        ],
                        [
                            'title' => 'Inert Materials',
                            'field' => 'mining_modifier.modifier_map.inert_materials',
                            'sortField' => 'MiningModule.Modifiers.InertMaterials',
                            ...suffix('%', space: false),
                        ],
                    ],
                ],

            ],
        ],
        'vehicle-flair-items' => [
            'title' => 'Vehicle Flair Items',
            'remove_fields' => ['grade', 'class', 'classification'],
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Description',
                    'field' => 'description.en_EN',
                    'headerSort' => false,
                ],
            ],
        ],
        'weapon-attachments' => [
            'title' => 'Personal Weapon Attachments',
            'remove_fields' => ['grade', 'class'],
            'shared' => ['occupancy'],
            'shared_insert_at' => 2,
            'add_columns_insert_at' => 1,
            'add_columns' => [
                [
                    'title' => 'Weapon Modifier',
                    'columns' => [
                        [
                            'title' => 'Damage',
                            'field' => 'weapon_modifier.base.damage_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Base.DamageMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Projectile Speed',
                            'field' => 'weapon_modifier.base.projectile_speed_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Base.ProjectileSpeedMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Ammo Cost',
                            'field' => 'weapon_modifier.base.ammo_cost_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Base.AmmoCostMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Audible Range',
                            'field' => 'weapon_modifier.base.sound_radius_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Base.SoundRadiusMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Muzzle Flash',
                            'field' => 'weapon_modifier.base.muzzle_flash_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Base.MuzzleFlashScale',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Heat Generation',
                            'field' => 'weapon_modifier.base.heat_generation_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Base.HeatGenerationMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                    ],
                ],

                [
                    'title' => 'Recoil',
                    'columns' => [
                        [
                            'title' => 'Recoil',
                            'field' => 'weapon_modifier.recoil.multiplier_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Recoil.RandomnessMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Decay',
                            'field' => 'weapon_modifier.recoil.decay_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Recoil.DecayMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                    ],
                ],

                [
                    'title' => 'Spread',
                    'columns' => [
                        [
                            'title' => 'Min',
                            'field' => 'weapon_modifier.spread.min_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Spread.MinMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'weapon_modifier.spread.max_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Spread.MaxMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'First Attack',
                            'field' => 'weapon_modifier.spread.first_attack_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Spread.FirstAttackMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Per Attack',
                            'field' => 'weapon_modifier.spread.per_attack_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Spread.AttackMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                        [
                            'title' => 'Decay',
                            'field' => 'weapon_modifier.spread.decay_change',
                            'sortField' => 'WeaponModifier.WeaponStats.Spread.DecayMultiplier',
                            'formatter' => 'pctDelta',
                        ],
                    ],
                ],

                [
                    'title' => 'Iron Sight',
                    'columns' => [
                        [
                            'title' => 'Default Range',
                            'field' => 'iron_sight.default_range',
                            'sortField' => 'WeaponAttachment.IronSight.DefaultRange',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max Range',
                            'field' => 'iron_sight.max_range',
                            'sortField' => 'WeaponAttachment.IronSight.MaxRange',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Range Increment',
                            'field' => 'iron_sight.range_increment',
                            'sortField' => 'WeaponAttachment.IronSight.RangeIncrement',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Auto Zeroing Time',
                            'field' => 'iron_sight.auto_zeroing_time',
                            'sortField' => 'WeaponAttachment.IronSight.AutoZeroingTime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Zoom Scale',
                            'field' => 'iron_sight.zoom_scale',
                            'sortField' => 'WeaponAttachment.IronSight.ZoomScale',
                            ...suffix('x'),
                        ],
                        [
                            'title' => 'Second Zoom Scale',
                            'field' => 'weapon_modifier.aim.second_zoom_scale',
                            'sortField' => 'WeaponModifier.WeaponStats.Aim.SecondZoomScale',
                            ...suffix('x'),
                        ],
                        [
                            'title' => 'Zoom Time',
                            'field' => 'iron_sight.zoom_time_change',
                            'sortField' => 'WeaponAttachment.IronSight.ZoomTimeScale',
                            'formatter' => 'pctDelta',
                        ],
                    ],
                ],

                [
                    'title' => 'Flashlight',
                    'columns' => [
                        [
                            'title' => 'Wide Mode',
                            'columns' => [
                                [
                                    'title' => 'Type',
                                    'field' => 'flashlight.wide.light_type',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Radius',
                                    'field' => 'flashlight.wide.light_radius',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Intensity',
                                    'field' => 'flashlight.wide.intensity',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Color',
                                    'field' => 'flashlight.wide.color_css',
                                    'formatter' => 'color',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                        [
                            'title' => 'Narrow Mode',
                            'columns' => [
                                [
                                    'title' => 'Type',
                                    'field' => 'flashlight.narrow.light_type',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Radius',
                                    'field' => 'flashlight.narrow.light_radius',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Intensity',
                                    'field' => 'flashlight.narrow.intensity',
                                    'headerSort' => false,
                                ],
                                [
                                    'title' => 'Color',
                                    'field' => 'flashlight.narrow.color_css',
                                    'formatter' => 'color',
                                    'headerSort' => false,
                                ],
                            ],
                        ],
                    ],
                ],

                [
                    'title' => 'Laser Pointer',
                    'columns' => [
                        [
                            'title' => 'Range',
                            'field' => 'laser_pointer.range',
                            'sortField' => 'WeaponAttachment.LaserPointer.Range',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Color',
                            'field' => 'laser_pointer.color_css',
                            'formatter' => 'color',
                            'headerSort' => false,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
