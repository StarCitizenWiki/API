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
        // 'weapon' => [
        //     'title' => 'Weapons',
        //     'add_columns' => [],
        //     'remove_fields' => [],
        //     'header_filter_options_map' => [],
        // ],
    ],
];
