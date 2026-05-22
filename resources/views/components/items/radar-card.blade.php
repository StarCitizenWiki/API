@use('App\Support\Format')
@props([
    'radar',
])

@php
    $sensitivity = data_get($radar, 'sensitivity', []);
    $groundVehicleSensitivity = data_get($radar, 'ground_vehicle_sensitivity', []);
    $piercing = data_get($radar, 'piercing', []);
    $aimAssist = data_get($radar, 'aim_assist', []);

    $sections = [];

    // Info
    $infoRows = array_values(array_filter([
        ['label' => 'Cooldown', 'value' => data_get($radar, 'cooldown') !== null ? Format::valueWithUnit(data_get($radar, 'cooldown'), 's', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    // Sensitivity
    $sensitivityRows = array_values(array_filter([
        ['label' => 'Infrared', 'value' => data_get($sensitivity, 'infrared') !== null ? Format::numberOrDash(data_get($sensitivity, 'infrared'), 2) : null],
        ['label' => 'Cross Section', 'value' => data_get($sensitivity, 'cross_section') !== null ? Format::numberOrDash(data_get($sensitivity, 'cross_section'), 2) : null],
        ['label' => 'Electromagnetic', 'value' => data_get($sensitivity, 'electromagnetic') !== null ? Format::numberOrDash(data_get($sensitivity, 'electromagnetic'), 2) : null],
        ['label' => 'Resource', 'value' => data_get($sensitivity, 'resource') !== null ? Format::numberOrDash(data_get($sensitivity, 'resource'), 2) : null],
        ['label' => 'dB', 'value' => data_get($sensitivity, 'db') !== null ? Format::numberOrDash(data_get($sensitivity, 'db'), 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($sensitivityRows !== []) {
        $sections[] = ['title' => 'Sensitivity', 'rows' => $sensitivityRows];
    }

    // Ground Vehicle Sensitivity
    $groundRows = array_values(array_filter([
        ['label' => 'Infrared', 'value' => data_get($groundVehicleSensitivity, 'infrared') !== null ? Format::numberOrDash(data_get($groundVehicleSensitivity, 'infrared'), 2) : null],
        ['label' => 'Cross Section', 'value' => data_get($groundVehicleSensitivity, 'cross_section') !== null ? Format::numberOrDash(data_get($groundVehicleSensitivity, 'cross_section'), 2) : null],
        ['label' => 'Electromagnetic', 'value' => data_get($groundVehicleSensitivity, 'electromagnetic') !== null ? Format::numberOrDash(data_get($groundVehicleSensitivity, 'electromagnetic'), 2) : null],
        ['label' => 'Resource', 'value' => data_get($groundVehicleSensitivity, 'resource') !== null ? Format::numberOrDash(data_get($groundVehicleSensitivity, 'resource'), 2) : null],
        ['label' => 'dB', 'value' => data_get($groundVehicleSensitivity, 'db') !== null ? Format::numberOrDash(data_get($groundVehicleSensitivity, 'db'), 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($groundRows !== []) {
        $sections[] = ['title' => 'Ground Vehicle Sensitivity', 'rows' => $groundRows];
    }

    // Aim Assist
    $aimAssistRows = array_values(array_filter([
        ['label' => 'Min Assignment Distance', 'value' => data_get($aimAssist, 'distance_min_assignment') !== null ? Format::valueWithUnit(data_get($aimAssist, 'distance_min_assignment'), 'm', 0) : null],
        ['label' => 'Max Assignment Distance', 'value' => data_get($aimAssist, 'distance_max_assignment') !== null ? Format::valueWithUnit(data_get($aimAssist, 'distance_max_assignment'), 'm', 0) : null],
        ['label' => 'Outside Range Buffer', 'value' => data_get($aimAssist, 'outside_range_buffer_distance') !== null ? Format::valueWithUnit(data_get($aimAssist, 'outside_range_buffer_distance'), 'm', 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($aimAssistRows !== []) {
        $sections[] = ['title' => 'Aim Assist', 'rows' => $aimAssistRows];
    }

    // Piercing
    $piercingRows = array_values(array_filter([
        ['label' => 'Infrared', 'value' => data_get($piercing, 'infrared') !== null ? Format::numberOrDash(data_get($piercing, 'infrared'), 2) : null],
        ['label' => 'Cross Section', 'value' => data_get($piercing, 'cross_section') !== null ? Format::numberOrDash(data_get($piercing, 'cross_section'), 2) : null],
        ['label' => 'Electromagnetic', 'value' => data_get($piercing, 'electromagnetic') !== null ? Format::numberOrDash(data_get($piercing, 'electromagnetic'), 2) : null],
        ['label' => 'Resource', 'value' => data_get($piercing, 'resource') !== null ? Format::numberOrDash(data_get($piercing, 'resource'), 2) : null],
        ['label' => 'dB', 'value' => data_get($piercing, 'db') !== null ? Format::numberOrDash(data_get($piercing, 'db'), 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($piercingRows !== []) {
        $sections[] = ['title' => 'Piercing', 'rows' => $piercingRows];
    }
@endphp

<x-data-card title="Radar" :sections="$sections" {{ $attributes }} />
