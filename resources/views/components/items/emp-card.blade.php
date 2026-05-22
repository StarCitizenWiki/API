@use('App\Support\Format')
@props([
    'emp',
])

@php
    $chargeDuration = data_get($emp, 'charge_duration');
    $unleashDuration = data_get($emp, 'unleash_duration');
    $cooldownDuration = data_get($emp, 'cooldown_duration');
    $distortionDamage = data_get($emp, 'distortion_damage');

    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'EMP Radius', 'value' => Format::range(data_get($emp, 'min_emp_radius'), data_get($emp, 'emp_radius'), 'm', 2)],
                ['label' => 'Charge Duration', 'value' => Format::valueWithUnit($chargeDuration, 's', 2)],
                ['label' => 'Unleash Duration', 'value' => Format::valueWithUnit($unleashDuration, 's', 2)],
                ['label' => 'Cooldown Duration', 'value' => Format::valueWithUnit($cooldownDuration, 's', 2)],
                ['label' => 'Distortion Damage', 'value' => Format::valueWithUnit($distortionDamage, 'N', 2)],
            ], static fn (array $row): bool => $row['value'] !== '-' && $row['value'] !== null)),
        ],
    ];
@endphp

<x-data-card title="EMP Generator" :sections="$sections" />
