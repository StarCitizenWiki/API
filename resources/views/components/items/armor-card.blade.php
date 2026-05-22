@use('App\Support\Format')
@props([
    'armor',
])

@php
    $health = data_get($armor, 'health');

    $signalMultipliers = data_get($armor, 'signal_multiplier', []);
    $signalMetrics = array_values(array_filter([
        ['label' => 'Cross Section', 'change' => data_get($signalMultipliers, 'cross_section_change')],
        ['label' => 'Infrared', 'change' => data_get($signalMultipliers, 'infrared_change')],
        ['label' => 'Electromagnetic', 'change' => data_get($signalMultipliers, 'electromagnetic_change')],
    ], static fn (array $m): bool => $m['change'] !== null));

    $damageMultipliers = data_get($armor, 'damage_multiplier', []);
    $damageMetrics = array_values(array_filter([
        ['label' => 'Physical', 'value' => data_get($damageMultipliers, 'physical'), 'change' => data_get($damageMultipliers, 'physical_change')],
        ['label' => 'Energy', 'value' => data_get($damageMultipliers, 'energy'), 'change' => data_get($damageMultipliers, 'energy_change')],
        ['label' => 'Distortion', 'value' => data_get($damageMultipliers, 'distortion'), 'change' => data_get($damageMultipliers, 'distortion_change')],
        ['label' => 'Thermal', 'value' => data_get($damageMultipliers, 'thermal'), 'change' => data_get($damageMultipliers, 'thermal_change')],
        ['label' => 'Biochemical', 'value' => data_get($damageMultipliers, 'biochemical'), 'change' => data_get($damageMultipliers, 'biochemical_change')],
        ['label' => 'Stun', 'value' => data_get($damageMultipliers, 'stun'), 'change' => data_get($damageMultipliers, 'stun_change')],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 1));

    $resistanceMultipliers = data_get($armor, 'resistance_multiplier', []);
    $resistanceMetrics = array_values(array_filter([
        ['label' => 'Physical', 'value' => data_get($resistanceMultipliers, 'physical_change')],
        ['label' => 'Energy', 'value' => data_get($resistanceMultipliers, 'energy_change')],
        ['label' => 'Distortion', 'value' => data_get($resistanceMultipliers, 'distortion_change')],
        ['label' => 'Thermal', 'value' => data_get($resistanceMultipliers, 'thermal_change')],
        ['label' => 'Biochemical', 'value' => data_get($resistanceMultipliers, 'biochemical_change')],
        ['label' => 'Stun', 'value' => data_get($resistanceMultipliers, 'stun_change')],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

    $penetrationResist = data_get($armor, 'deflection', []);
    $penetrationMetrics = array_values(array_filter([
        ['label' => 'Base', 'value' => data_get($penetrationResist, 'base')],
        ['label' => 'Physical', 'value' => data_get($penetrationResist, 'physical')],
        ['label' => 'Energy', 'value' => data_get($penetrationResist, 'energy')],
        ['label' => 'Distortion', 'value' => data_get($penetrationResist, 'distortion')],
        ['label' => 'Thermal', 'value' => data_get($penetrationResist, 'thermal')],
        ['label' => 'Biochemical', 'value' => data_get($penetrationResist, 'biochemical')],
        ['label' => 'Stun', 'value' => data_get($penetrationResist, 'stun')],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

    $sections = [];

    if ($health !== null) {
        $sections[] = ['title' => 'Info', 'rows' => [['label' => 'Health', 'value' => Format::valueWithUnit($health, 'HP', 0)]]];
    }

    if ($penetrationMetrics !== []) {
        $sections[] = ['title' => 'Deflection', 'rows' => collect($penetrationMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::numberOrDash($m['value'], 2)])->all()];
    }

    if ($signalMetrics !== []) {
        $sections[] = ['title' => 'Detection Signal', 'rows' => collect($signalMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['change'] * 100, '%', 1, sign: true), 'class' => Format::colorClass($m['change'])])->all()];
    }

    if ($resistanceMetrics !== []) {
        $sections[] = ['title' => 'Resistance', 'rows' => collect($resistanceMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'] * 100, '%', 1), 'class' => Format::colorClass($m['value'], true)])->all()];
    }

    if ($damageMetrics !== []) {
        $sections[] = ['title' => 'Damage Multipliers', 'rows' => collect($damageMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['change'] * 100, '%', 1, sign: true), 'class' => Format::colorClass($m['change'])])->all()];
    }
@endphp

<x-data-card title="Armor" :sections="$sections" />
