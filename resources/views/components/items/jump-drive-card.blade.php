@use('App\Support\Format')
@props([
    'jumpDrive',
])

@php
    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'Fuel Usage Efficiency', 'value' => Format::valueWithUnit(data_get($jumpDrive, 'fuel_usage_efficiency_multiplier'), 'x', 2)],
                ['label' => 'Alignment Rate', 'value' => Format::valueWithUnit(data_get($jumpDrive, 'alignment_rate'), '', 2)],
                ['label' => 'Alignment Decay Rate', 'value' => Format::valueWithUnit(data_get($jumpDrive, 'alignment_decay_rate'), '', 2)],
                ['label' => 'Tuning Rate', 'value' => Format::valueWithUnit(data_get($jumpDrive, 'tuning_rate'), '', 2)],
                ['label' => 'Tuning Decay Rate', 'value' => Format::valueWithUnit(data_get($jumpDrive, 'tuning_decay_rate'), '', 2)],
            ], static fn (array $row): bool => $row['value'] !== '-')),
        ],
    ];
@endphp

<x-data-card title="Jump Drive" :sections="$sections" />
