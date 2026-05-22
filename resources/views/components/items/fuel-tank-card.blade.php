@use('App\Support\Format')
@props([
    'fuelTank',
])

@php
    $fillRate = data_get($fuelTank, 'fill_rate');
    $drainRate = data_get($fuelTank, 'drain_rate');
    $capacity = data_get($fuelTank, 'capacity');

    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'Fill Rate', 'value' => Format::valueWithUnit($fillRate, '/s', 2)],
                ['label' => 'Drain Rate', 'value' => Format::valueWithUnit($drainRate, '/s', 2)],
                ['label' => 'Capacity', 'value' => Format::valueWithUnit($capacity, 'SCU', 0)],
            ], static fn (array $row): bool => $row['value'] !== '-')),
        ],
    ];
@endphp

<x-data-card title="Fuel Tank" :sections="$sections" />
