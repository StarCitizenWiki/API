@use('App\Support\Format')
@props([
    'fuelIntake',
])

@php
    $fuelPushRate = data_get($fuelIntake, 'fuel_push_rate');
    $minimumRate = data_get($fuelIntake, 'minimum_rate');

    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'Fuel Push Rate', 'value' => Format::valueWithUnit($fuelPushRate, '/s', 2)],
                ['label' => 'Minimum Rate', 'value' => Format::valueWithUnit($minimumRate, '/s', 2)],
            ], static fn (array $row): bool => $row['value'] !== '-')),
        ],
    ];
@endphp

<x-data-card title="Fuel Intake" :sections="$sections" />
