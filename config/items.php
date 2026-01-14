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
        'resourceNetwork' => [
            'title' => 'Resource Network',
            'columns' => [
                //                [
                //                    'title' => 'Networked',
                //                    'field' => 'resource_network.is_networked',
                //                    'formatter' => 'tickCross',
                //                ],
                [
                    'title' => 'Repairable',
                    'field' => 'resource_network.repair.max_repair_count',
                    'formatter' => 'tickCross',
                ],
                [
                    'title' => 'Pwr. Usage',
                    'field' => 'resource_network.usage.power.maximum',
                ],
                [
                    'title' => 'Clnt. Usage',
                    'field' => 'resource_network.usage.coolant.maximum',
                ],
            ],
        ],
        'resourceNetwork.power_usage' => [
            'title' => 'Power Usage',
            'columns' => [
                [
                    'title' => 'Min',
                    'field' => 'resource_network.usage.power.minimum',
                ],
                [
                    'title' => 'Max',
                    'field' => 'resource_network.usage.power.maximum',
                ],
            ],
        ],
        'resourceNetwork.cooling_usage' => [
            'title' => 'Cooling Usage',
            'columns' => [
                [
                    'title' => 'Min',
                    'field' => 'resource_network.usage.coolant.minimum',
                ],
                [
                    'title' => 'Max',
                    'field' => 'resource_network.usage.coolant.maximum',
                ],
            ],
        ],
        'resourceNetwork.repair' => [
            'title' => 'Repair',
            'columns' => [
                [
                    'title' => 'Count',
                    'field' => 'resource_network.repair.max_repair_count',
                ],
                [
                    'title' => 'Time',
                    'field' => 'resource_network.repair.time_to_repair',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Health Ratio',
                    'field' => 'resource_network.repair.health_ratio',
                    'formatter' => 'pct',
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
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'emission.em_max',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Decay',
                            'field' => 'emission.em_decay',
                            ...numFormat(),
                        ],
                    ],
                ],
                [
                    'title' => 'IR Start',
                    'field' => 'emission.ir',
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
                    ...suffix('HP'),
                ],
                [
                    'title' => 'Shutdown Dmg',
                    'field' => 'distortion.maximum',
                    ...numFormat(),
                ],
                [
                    'title' => 'Shutdown Time',
                    'field' => 'distortion.shutdown_time',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Decay Rate',
                    'field' => 'distortion.decay_rate',
                    ...numFormat(),
                ],
                [
                    'title' => 'Decay Delay',
                    'field' => 'distortion.decay_delay',
                    ...suffix('s', space: false),
                ],
                [
                    'title' => 'Damage Multiplier',
                    'columns' => [
                        [
                            'title' => 'Physical',
                            'field' => 'durability.resistance.physical',
                            'formatter' => 'pct',
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'durability.resistance.energy',
                            'formatter' => 'pct',
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'durability.resistance.distortion',
                            'formatter' => 'pct',
                        ],
                        [
                            'title' => 'Thermal',
                            'field' => 'durability.resistance.thermal',
                            'formatter' => 'pct',
                        ],
                        [
                            'title' => 'Biochemical',
                            'field' => 'durability.resistance.biochemical',
                            'formatter' => 'pct',
                        ],
                        [
                            'title' => 'Stun',
                            'field' => 'durability.resistance.stun',
                            'formatter' => 'pct',
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
                ],
                [
                    'title' => 'IR Threshold',
                    'field' => 'temperature.ir_threshold',
                    ...suffix('°C'),
                ],
                [
                    'title' => 'Overheat',
                    'field' => 'temperature.overheat_temperature',
                    ...suffix('°C'),
                ],
                [
                    'title' => 'Max',
                    'field' => 'temperature.max_temperature',
                    ...suffix('°C'),
                ],
                [
                    'title' => 'Recovery',
                    'field' => 'temperature.recovery_temperature',
                    ...suffix('°C'),
                ],
            ],
        ],
        'damage_multiplier' => [
            'title' => 'Damage Multiplier',
            'columns' => [
                [
                    'title' => 'Physical',
                    'field' => 'durability.resistance.physical',
                ],
                [
                    'title' => 'Energy',
                    'field' => 'durability.resistance.energy',
                ],
                [
                    'title' => 'Distortion',
                    'field' => 'durability.resistance.distortion',
                ],
                [
                    'title' => 'Thermal',
                    'field' => 'durability.resistance.thermal',
                ],
                [
                    'title' => 'Biochemical',
                    'field' => 'durability.resistance.biochemical',
                ],
                [
                    'title' => 'Stun',
                    'field' => 'durability.resistance.stun',
                ],
            ],
        ],
        'occupancy' => [
            'title' => 'Occupancy',
            'columns' => [
                [
                    'title' => 'Mass',
                    'field' => 'mass',
                    ...suffix('kg'),
                ],
                [
                    'title' => 'Weight',
                    'field' => 'dimension.volume',
                    ...suffix('SCU'),
                ],
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
        'Bomb' => [
            'title' => 'Bombs',
            'shared' => ['durability', 'occupancy'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Damages',
                    'columns' => [
                        [
                            'title' => 'Total',
                            'field' => 'bomb.damage_total',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Physical',
                            'field' => 'bomb.damage_map.physical',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'bomb.damage_map.energy',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'bomb.damage_map.distortion',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Thermal',
                            'field' => 'bomb.damage_map.thermal',
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
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'bomb.explosion.radius_max',
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Safety Distance',
                            'field' => 'bomb.explosion.safety_distance',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Proximity',
                            'field' => 'bomb.explosion.proximity',
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
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Ignite',
                            'field' => 'bomb.delays.ignite_time',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Collision Delay',
                            'field' => 'bomb.delays.collision_delay_time',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
            ],
        ],
        'Cooler' => [
            'title' => 'Coolers',
            'shared' => ['resourceNetwork.power_usage', 'resourceNetwork.emission', 'resourceNetwork.repair', 'durability', 'occupancy'],
            'shared_insert_at' => -1,
            'add_columns_insert_at' => -2,
            'add_columns' => [
                [
                    'title' => 'Coolant Generation',
                    'field' => 'resource_network.generation.coolant',
                ],
            ],
        ],
        'EMP' => [
            'title' => 'EMP',
            'shared' => ['resourceNetwork.repair', 'durability', 'occupancy'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Damage',
                    'field' => 'emp.distortion_damage',
                    ...numFormat(),
                ],
                [
                    'title' => 'Radius',
                    'columns' => [
                        [
                            'title' => 'Min',
                            'field' => 'emp.min_emp_radius',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max',
                            'field' => 'emp.emp_radius',
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
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Unleash',
                            'field' => 'emp.unleash_duration',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'emp.cooldown_duration',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],

            ],
        ],
        'FlightController' => [
            'title' => 'Flight Blades',
            'shared' => ['resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'resourceNetwork.repair'],
            'shared_insert_at' => -1,
            'add_columns_insert_at' => -2,
            'add_columns' => [
                [
                    'title' => 'Speed',
                    'columns' => [
                        [
                            'title' => 'SCM',
                            'field' => 'flight_controller.scm_speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'NAV',
                            'field' => 'flight_controller.max_speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Boost Forward',
                            'field' => 'flight_controller.boost_speed_forward',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Boost Backward',
                            'field' => 'flight_controller.boost_speed_backward',
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
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Yaw',
                            'field' => 'flight_controller.yaw',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Roll',
                            'field' => 'flight_controller.roll',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Pitch (Boosted)',
                            'field' => 'flight_controller.pitch_boosted',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Yaw (Boosted)',
                            'field' => 'flight_controller.yaw_boosted',
                            ...suffix('º/s'),
                        ],
                        [
                            'title' => 'Roll (Boosted)',
                            'field' => 'flight_controller.roll_boosted',
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
                        ],
                        [
                            'title' => 'Angular',
                            'field' => 'flight_controller.thruster_decay.angular_accel',
                        ],
                    ],
                ],
                [
                    'title' => 'Multiplier',
                    'columns' => [
                        [
                            'title' => 'Lift',
                            'field' => 'flight_controller.multiplier.lift',
                        ],
                        [
                            'title' => 'Drag',
                            'field' => 'flight_controller.multiplier.drag',
                        ],
                        [
                            'title' => 'SCM Drag',
                            'field' => 'flight_controller.multiplier.scm_max_drag',
                        ],
                        [
                            'title' => 'Torque Imbalance',
                            'field' => 'flight_controller.multiplier.torque_imbalance',
                        ],
                        [
                            'title' => 'Precision Landing',
                            'field' => 'flight_controller.multiplier.precision_landing',
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
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Ramp-Up',
                            'field' => 'flight_controller.boost_activation.ramp_up_time',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Ramp-Down',
                            'field' => 'flight_controller.boost_activation.ramp_down_time',
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
                                ],
                                [
                                    'title' => '-',
                                    'field' => 'flight_controller.boost_multiplier.accel_x.negative',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Y',
                            'columns' => [
                                [
                                    'title' => '+',
                                    'field' => 'flight_controller.boost_multiplier.accel_y.positive',
                                ],
                                [
                                    'title' => '-',
                                    'field' => 'flight_controller.boost_multiplier.accel_y.negative',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Z',
                            'columns' => [
                                [
                                    'title' => '+',
                                    'field' => 'flight_controller.boost_multiplier.accel_z.positive',
                                ],
                                [
                                    'title' => '-',
                                    'field' => 'flight_controller.boost_multiplier.accel_z.negative',
                                ],
                            ],
                        ],
                        [
                            'title' => 'Pitch',
                            'field' => 'flight_controller.boost_multiplier.pitch',
                        ],
                        [
                            'title' => 'Yaw',
                            'field' => 'flight_controller.boost_multiplier.yaw',
                        ],
                        [
                            'title' => 'Roll',
                            'field' => 'flight_controller.boost_multiplier.roll',
                        ],

                        [
                            'title' => 'Pitch Accel.',
                            'field' => 'flight_controller.boost_multiplier.pitch_accel',
                        ],
                        [
                            'title' => 'Yaw Accel.',
                            'field' => 'flight_controller.boost_multiplier.yaw_accel',
                        ],
                        [
                            'title' => 'Roll Accel.',
                            'field' => 'flight_controller.boost_multiplier.roll_accel',
                        ],
                    ],
                ],
                [
                    'title' => 'Boost Capacitor',
                    'columns' => [
                        [
                            'title' => 'Capacity',
                            'field' => 'flight_controller.boost_capacitor.capacity',
                        ],
                        [
                            'title' => 'Threshold Ratio',
                            'field' => 'flight_controller.boost_capacitor.threshold_ratio',
                        ],
                        [
                            'title' => 'Idle Cost',
                            'field' => 'flight_controller.boost_capacitor.idle_cost',
                        ],
                        [
                            'title' => 'Linear Cost',
                            'field' => 'flight_controller.boost_capacitor.linear_cost',
                        ],
                        [
                            'title' => 'Angular Cost',
                            'field' => 'flight_controller.boost_capacitor.angular_cost',
                        ],
                        [
                            'title' => 'Regen Delay',
                            'field' => 'flight_controller.boost_capacitor.regen_delay',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Regen per Sec',
                            'field' => 'flight_controller.boost_capacitor.regen_per_sec',
                        ],
                        [
                            'title' => 'Regen Time',
                            'field' => 'flight_controller.boost_capacitor.regen_time',
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
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Turn Friction',
                            'field' => 'flight_controller.gravlev.turn_friction',
                        ],
                        [
                            'title' => 'Air Controller Multiplier',
                            'field' => 'flight_controller.gravlev.air_controller_multiplier',
                        ],
                        [
                            'title' => 'Anti-Fall Multiplier',
                            'field' => 'flight_controller.gravlev.anti_fall_multiplier',
                        ],
                        [
                            'title' => 'Lateral Strafe Multiplier',
                            'field' => 'flight_controller.gravlev.lateral_strafe_multiplier',
                        ],
                    ],
                ],
            ],
        ],
        'JumpDrive' => [
            'title' => 'Jump Drives',
            'shared' => ['resourceNetwork.repair', 'durability', 'occupancy'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Alignment Rate',
                    'field' => 'jump_drive.alignment_rate',
                ],
                [
                    'title' => 'Alignment Decay Rate',
                    'field' => 'jump_drive.alignment_decay_rate',
                ],
                [
                    'title' => 'Tuning Rate',
                    'field' => 'jump_drive.tuning_rate',
                ],
                [
                    'title' => 'Tuning Decay Rate',
                    'field' => 'jump_drive.tuning_decay_rate',
                ],
                [
                    'title' => 'Fuel Usage Efficiency Multiplier',
                    'field' => 'jump_drive.fuel_usage_efficiency_multiplier',
                ],
            ],
        ],
        'MissileLauncher' => [
            'title' => 'Missile Racks',
            'shared' => ['resourceNetwork.repair', 'occupancy'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Capacity',
                    'field' => 'missile_rack.missile_count',
                ],
                [
                    'title' => 'Size',
                    'field' => 'missile_rack.missile_size',
                ],

            ],
        ],
        'Missile' => [
            'title' => 'Missiles & Torpedoes',
            'shared' => ['occupancy'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Tracking',
                    'columns' => [
                        [
                            'title' => 'Signal',
                            'field' => 'missile.signal_type',
                        ],
                        [
                            'title' => 'Min',
                            'field' => 'missile.tracking_signal_min',
                        ],
                    ],
                ],
                [
                    'title' => 'Damages',
                    'columns' => [
                        [
                            'title' => 'Total',
                            'field' => 'missile.damage_total',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Physical',
                            'field' => 'missile.damage_map.physical',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Energy',
                            'field' => 'missile.damage_map.energy',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Distortion',
                            'field' => 'missile.damage_map.distortion',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Thermal',
                            'field' => 'missile.damage_map.thermal',
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
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'missile.explosion.radius_max',
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Safety Distance',
                            'field' => 'missile.explosion.safety_distance',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Proximity',
                            'field' => 'missile.explosion.proximity',
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
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Lock',
                            'field' => 'missile.delays.lock_time',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Ignite',
                            'field' => 'missile.delays.ignite_time',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Collision Delay',
                            'field' => 'missile.delays.collision_delay_time',
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
                        ],
                        [
                            'title' => 'Range',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'missile.target_lock.range_min',
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'missile.target_lock.range_max',
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Angle',
                            'field' => 'missile.target_lock.angle',
                            ...suffix('°', space: false),
                        ],
                        [
                            'title' => 'Fire Without Lock',
                            'field' => 'missile.target_lock.allow_dumb_firing',
                        ],
                    ],
                ],
                [
                    'title' => 'Flight Characteristics',
                    'columns' => [
                        [
                            'title' => 'Speed',
                            'field' => 'missile.flight.speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Range',
                            'field' => 'missile.flight.range',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max Lifetime',
                            'field' => 'missile.flight.max_lifetime',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Boost Duration',
                            'field' => 'missile.flight.boost_phase_duration',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Terminal Phase Time',
                            'field' => 'missile.flight.terminal_phase_engagement_time',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Terminal Phase Angle',
                            'field' => 'missile.flight.terminal_phase_engagement_angle',
                            ...suffix('°', space: false),
                        ],
                        [
                            'title' => 'Boost Speed',
                            'field' => 'missile.flight.boost_speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Intercept Speed',
                            'field' => 'missile.flight.intercept_speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Terminal Speed',
                            'field' => 'missile.flight.terminal_speed',
                            ...suffix('m/s'),
                        ],
                    ],
                ],
            ],
        ],
        'Paints' => [
            'title' => 'Vehicle Paints',
            'shared' => ['occupancy'],
            'add_columns' => [
                [
                    'title' => 'Description',
                    'field' => 'description.en_EN',
                ],
            ],
        ],
        'PowerPlant' => [
            'title' => 'Power Plants',
            'shared' => ['resourceNetwork.cooling_usage', 'resourceNetwork.repair', 'temperature', 'durability', 'occupancy'],
            'shared_insert_at' => -1,
            'add_columns_insert_at' => -2,
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
                                    ...numFormat(),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'emission.em_max',
                                    ...numFormat(),
                                ],
                                [
                                    'title' => 'Decay',
                                    'field' => 'emission.em_decay',
                                    ...numFormat(),
                                ],
                            ],
                        ],
                        [
                            'title' => 'IR Start',
                            'field' => 'emission.ir',
                            ...numFormat(),
                        ],
                    ],
                ],

            ],
        ],
        'QuantumDrive' => [
            'title' => 'Quantum Drives',
            'shared' => ['resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'resourceNetwork.repair', 'resourceNetwork.emission', 'temperature', 'durability', 'occupancy'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Travel',
                    'columns' => [
                        [
                            [
                                'title' => 'SCU per GM',
                                'field' => 'quantum_drive.fuel_consumption_scu_per_gm',
                            ],
                            [
                                'title' => 'Efficiency',
                                'field' => 'quantum_drive.fuel_efficiency',
                            ],
                            [
                                'title' => 'Time per 10GM',
                                'field' => 'quantum_drive.travel_time_10gm.formatted',
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Delays',
                    'columns' => [
                        [
                            [
                                'title' => 'Spool-Up',
                                'field' => 'quantum_drive.standard_jump.spool_up_time',
                                ...suffix('s', space: false),
                            ],
                            [
                                'title' => 'Cooldown',
                                'field' => 'quantum_drive.standard_jump.cooldown_time',
                                ...suffix('s', space: false),
                            ],
                            [
                                'title' => 'Interdiction',
                                'field' => 'quantum_drive.standard_jump.interdiction_effect_time',
                                ...suffix('s', space: false),
                            ],
                            [
                                'title' => 'Calibration',
                                'field' => 'quantum_drive.standard_jump.calibration_delay_in_seconds',
                                ...suffix('s', space: false),
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Speed',
                    'columns' => [
                        [
                            [
                                'title' => 'Max',
                                'field' => 'quantum_drive.standard_jump.drive_speed',
                                ...suffix('m/s'),
                            ],
                            [
                                'title' => 'Stage 1 Accel.',
                                'field' => 'quantum_drive.standard_jump.stage_one_acceleration',
                                ...suffix('m/s/s'),
                            ],
                            [
                                'title' => 'Stage 2 Accel.',
                                'field' => 'quantum_drive.standard_jump.stage_two_acceleration',
                                ...suffix('m/s/s'),
                            ],
                            [
                                'title' => 'Spline',
                                'field' => 'quantum_drive.spline.drive_speed',
                                ...suffix('m/s'),
                            ],
                        ],
                    ],
                ],

            ],
        ],
        'QuantumInterdictionGenerator' => [
            'title' => 'Quantum Interdiction Generators',
            'shared' => ['resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'resourceNetwork.emission'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Radius',
                    'columns' => [
                        [
                            'title' => 'Jamming',
                            'field' => 'quantum_interdiction_generator.jammer_range',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Interdiction',
                            'field' => 'quantum_interdiction_generator.interdiction_range',
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
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Activation',
                            'field' => 'quantum_interdiction_generator.pulse.activation_duration',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Disperse Charge',
                            'field' => 'quantum_interdiction_generator.pulse.disperse_charge_duration',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Discharge',
                            'field' => 'quantum_interdiction_generator.pulse.discharge_duration',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Cooldown',
                            'field' => 'quantum_interdiction_generator.pulse.cooldown_duration',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],

            ],
        ],
        'Radar' => [
            'title' => 'Radar',
            'shared' => ['resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'resourceNetwork.emission', 'resourceNetwork.repair', 'temperature', 'durability', 'occupancy'],
            'shared_insert_at' => -1,
            'add_columns_insert_at' => -2,
            'add_columns' => [
                [
                    'title' => 'Sensitivity',
                    'columns' => [
                        [
                            'title' => 'IR',
                            'field' => 'radar.sensitivity.infrared',
                        ],
                        [
                            'title' => 'CS',
                            'field' => 'radar.sensitivity.cross_section',
                        ],
                        [
                            'title' => 'EM',
                            'field' => 'radar.sensitivity.electromagnetic',
                        ],
                        [
                            'title' => 'RS',
                            'field' => 'radar.sensitivity.resource',
                        ],
                        [
                            'title' => 'dB',
                            'field' => 'radar.sensitivity.db',
                        ],
                    ],
                ],
                [
                    'title' => 'Ground Vehicle Sensitivity',
                    'columns' => [
                        [
                            'title' => 'IR',
                            'field' => 'radar.ground_vehicle_sensitivity.infrared',
                        ],
                        [
                            'title' => 'CS',
                            'field' => 'radar.ground_vehicle_sensitivity.cross_section',
                        ],
                        [
                            'title' => 'EM',
                            'field' => 'radar.ground_vehicle_sensitivity.electromagnetic',
                        ],
                        [
                            'title' => 'RS',
                            'field' => 'radar.ground_vehicle_sensitivity.resource',
                        ],
                        [
                            'title' => 'dB',
                            'field' => 'radar.ground_vehicle_sensitivity.db',
                        ],
                    ],
                ],
                [
                    'title' => 'Piercing',
                    'columns' => [
                        [
                            'title' => 'IR',
                            'field' => 'radar.piercing.infrared',
                        ],
                        [
                            'title' => 'CS',
                            'field' => 'radar.piercing.cross_section',
                        ],
                        [
                            'title' => 'EM',
                            'field' => 'radar.piercing.electromagnetic',
                        ],
                        [
                            'title' => 'RS',
                            'field' => 'radar.piercing.resource',
                        ],
                        [
                            'title' => 'dB',
                            'field' => 'radar.piercing.db',
                        ],
                    ],
                ],
            ],
        ],
        'SalvageModifier' => [
            'title' => 'Salvage Modules',
            'shared' => ['occupancy'],
            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Modifiers',
                    'columns' => [
                        [
                            'title' => 'Salvage Speed',
                            'field' => 'salvage_modifier.salvage_speed_multiplier',
                        ],
                        [
                            'title' => 'Radius',
                            'field' => 'salvage_modifier.radius_multiplier',
                        ],
                        [
                            'title' => 'Extraction Efficiency',
                            'field' => 'salvage_modifier.extraction_efficiency',
                        ],
                    ],
                ],
            ],
        ],
        'SelfDestruct' => [
            'title' => 'Self Destructs',
            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Self Destruct',
                    'columns' => [
                        [
                            'title' => 'Damage',
                            'field' => 'self_destruct.damage',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Countdown',
                            'field' => 'self_destruct.countdown',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Radius',
                            'columns' => [
                                [
                                    'title' => 'Min',
                                    'field' => 'self_destruct.min_radius',
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'self_destruct.phys_radius',
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'Shield' => [
            'title' => 'Shields',
            'shared' => ['resourceNetwork.power_usage', 'resourceNetwork.cooling_usage', 'resourceNetwork.emission', 'resourceNetwork.repair', 'temperature', 'durability', 'occupancy'],
            'shared_insert_at' => -1,
            'add_columns_insert_at' => -2,
            'add_columns' => [
                [
                    'title' => 'Shield',
                    'columns' => [
                        [
                            'title' => 'Health',
                            'field' => 'shield.max_health',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Regen Rate',
                            'field' => 'shield.regen_rate',
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Regen Time',
                            'field' => 'shield.regen_rate',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Damage Delay',
                            'field' => 'shield.regen_delay.damage',
                            ...suffix('s', space: false),
                        ],
                        [
                            'title' => 'Downed Delay',
                            'field' => 'shield.regen_delay.downed',
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
                            ...numFormat(),
                        ],
                        [
                            'title' => 'Regen Time',
                            'field' => 'shield.reserve_pool.regen_time',
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
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.absorption.physical.min',
                                    'formatter' => 'pct',
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
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.absorption.energy.min',
                                    'formatter' => 'pct',
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
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.absorption.distortion.min',
                                    'formatter' => 'pct',
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
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.resistance.physical.min',
                                    'formatter' => 'pct',
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
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.resistance.energy.min',
                                    'formatter' => 'pct',
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
                                ],
                                [
                                    'title' => 'Min',
                                    'field' => 'shield.resistance.distortion.min',
                                    'formatter' => 'pct',
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
            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Face Type',
                    'field' => 'shield_controller.face_type',
                ],
                [
                    'title' => 'Max Reallocation',
                    'field' => 'shield_controller.max_reallocation',
                ],
                [
                    'title' => 'Reconfiguration Cooldown',
                    'field' => 'shield_controller.reconfiguration_cooldown',
                ],
                [
                    'title' => 'Max Electrical Charge Dmg Rate',
                    'field' => 'shield_controller.max_electrical_charge_damage_rate',
                ],
            ],
        ],
        'TractorBeam,TowingBeam' => [
            'title' => 'Tractor & Towing Beams',
            'shared' => ['resourceNetwork.emission', 'resourceNetwork.power_usage', 'resourceNetwork.repair', 'durability', 'occupancy'],
            'add_columns_insert_at' => -1,
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
                                    ...suffix('N'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'tractor_beam.force.max',
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
                                    ...suffix('m'),
                                ],
                                [
                                    'title' => 'Max',
                                    'field' => 'tractor_beam.range.max',
                                    ...suffix('m'),
                                ],
                            ],
                        ],
                        [
                            'title' => 'Full Strength',
                            'field' => 'tractor_beam.range.full_strength_distance',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Max Angle',
                            'field' => 'tractor_beam.range.max_angle',
                            ...suffix('°'),
                        ],
                        [
                            'title' => 'Max Volume',
                            'field' => 'tractor_beam.force.max_volume',
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
                            ...suffix('N'),
                        ],
                        [
                            'title' => 'Max Acceleration',
                            'field' => 'tractor_beam.towing.max_acceleration',
                            ...suffix('m/s²'),
                        ],
                        [
                            'title' => 'Max Distance',
                            'field' => 'tractor_beam.towing.max_distance',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'QT Mass Limit',
                            'field' => 'tractor_beam.towing.qt_mass_limit',
                            ...suffix('kg'),
                        ],
                    ],
                ],
            ],
        ],
        'Turret' => [
            'title' => 'Turrets & Gimbals',
            'shared' => ['resourceNetwork.repair', 'durability', 'occupancy'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Mounts',
                    'columns' => [
                        [
                            'title' => 'Count',
                            'field' => 'turret.mounts',
                        ],
                        [
                            'title' => 'Size Min',
                            'field' => 'turret.min_size',
                        ],
                        [
                            'title' => 'Size Max',
                            'field' => 'turret.max_size',
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
                        ],
                        [
                            'title' => 'Time to full Speed',
                            'field' => 'turret.yaw_axis.time_to_full_speed',
                            ...suffix('s', space: false),
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
                        ],
                        [
                            'title' => 'Time to full Speed',
                            'field' => 'turret.pitch_axis.time_to_full_speed',
                            ...suffix('s', space: false),
                        ],
                    ],
                ],
            ],
        ],
        'WeaponDefensive' => [
            'title' => 'Countermeasures',
            'shared' => ['occupancy'],

            'add_columns_insert_at' => -1,
            'add_columns' => [
                [
                    'title' => 'Type',
                    'field' => 'counter_measure.type',
                ],
                [
                    'title' => 'Ammo',
                    'columns' => [
                        [
                            'title' => 'Capacity',
                            'field' => 'ammunition.capacity',
                        ],
                        [
                            'title' => 'Speed',
                            'field' => 'ammunition.speed',
                            ...suffix('m/s'),
                        ],
                        [
                            'title' => 'Range',
                            'field' => 'ammunition.range',
                            ...suffix('m'),
                        ],
                        [
                            'title' => 'Lifetime',
                            'field' => 'ammunition.lifetime',
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
                        ],
                        [
                            'title' => 'CS',
                            'field' => 'counter_measure.signature.cross_section',
                        ],
                        [
                            'title' => 'EM',
                            'field' => 'counter_measure.signature.electromagnetic',
                        ],
                        [
                            'title' => 'dB',
                            'field' => 'counter_measure.signature.decibel',
                        ],
                    ],
                ],
            ],
        ],

        'vehicle-flair-items' => [
            'title' => 'Vehicle Flair Items',
            'add_columns' => [
                [
                    'title' => 'Description',
                    'field' => 'description.en_EN',
                ],
            ],
        ],

        // 'weapon' => [
        //     'title' => 'Weapons',
        //     'add_columns' => [],
        //     'remove_fields' => [],
        //     'header_filter_options_map' => [],
        // ],
    ],
];
