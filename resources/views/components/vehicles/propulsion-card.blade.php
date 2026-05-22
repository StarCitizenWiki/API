@use('App\Support\Format')
@props(['vehicle'])

@php
    $fuel = data_get($vehicle, 'fuel', []);
    $quantum = data_get($vehicle, 'quantum', []);

    $capacityMetrics = [
        [
            'label' => 'Fuel',
            'value' => data_get($fuel, 'capacity'),
            'unit' => 'SCU',
            'precision' => 0,
        ],
        [
            'label' => 'Quantum',
            'value' => data_get($quantum, 'quantum_fuel_capacity'),
            'unit' => 'SCU',
            'precision' => 2,
        ],
    ];

    $capacityMetrics = array_values(array_filter(
        $capacityMetrics,
        static fn (array $metric): bool => $metric['value'] !== null && $metric['value'] > 0
    ));

    $travelMetrics = [
        [
            'label' => 'Speed',
            'value' => data_get($quantum, 'quantum_speed'),
            'formatter' => static fn (mixed $value): string => Format::compact($value, 0).' m/s',
        ],
        [
            'label' => 'Spool Time',
            'value' => data_get($quantum, 'quantum_spool_time'),
            'formatter' => static fn (mixed $value): string => Format::valueWithUnit($value, 's', 2),
        ],
        [
            'label' => 'Range',
            'value' => data_get($quantum, 'quantum_range'),
            'formatter' => static fn (mixed $value): string => Format::compact($value, 2).' m',
        ],
    ];

    $travelMetrics = array_values(array_filter(
        $travelMetrics,
        static fn (array $metric): bool => $metric['value'] !== null
    ));

    $sections = [];

    if ($capacityMetrics !== []) {
        $sections[] = [
            'title' => 'Capacity',
            'rows' => array_map(static fn (array $metric): array => [
                'label' => $metric['label'],
                'value' => Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']),
            ], $capacityMetrics),
        ];
    }

    if ($travelMetrics !== []) {
        $sections[] = [
            'title' => 'Travel',
            'rows' => array_map(static fn (array $metric): array => [
                'label' => $metric['label'],
                'value' => $metric['formatter']($metric['value']),
            ], $travelMetrics),
        ];
    }
@endphp

@if ($sections !== [])
    <x-data-card title="Fuel & Quantum" :sections="$sections" {{ $attributes }} />
@endif
