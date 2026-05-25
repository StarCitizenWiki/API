@use('App\Support\Format')
@props([
    'salvageModifier',
])

@php
    $sections = [];

    $speed = data_get($salvageModifier, 'salvage_speed_multiplier');
    $radius = data_get($salvageModifier, 'radius_multiplier');
    $efficiency = data_get($salvageModifier, 'extraction_efficiency');

    $rows = array_values(array_filter([
        $speed !== null ? ['label' => 'Salvage Speed', 'value' => Format::valueWithUnit($speed, '×', 2)] : null,
        $radius !== null ? ['label' => 'Radius', 'value' => Format::valueWithUnit($radius, '×', 2)] : null,
        $efficiency !== null ? ['label' => 'Extraction Efficiency', 'value' => Format::valueWithUnit($efficiency, '×', 2)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($rows !== []) {
        $sections[] = ['title' => 'Modifiers', 'rows' => $rows];
    }
@endphp

<x-data-card title="Salvage Modifier" :sections="$sections" />
