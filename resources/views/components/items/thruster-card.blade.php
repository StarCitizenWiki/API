@use('App\Support\Format')
@props([
    'thruster',
])

@php
    $role = data_get($thruster, 'role');
    $vtolOnly = data_get($thruster, 'vtol_only');

    $performance = data_get($thruster, 'performance', []);
    $thrustCapacity = data_get($performance, 'thrust_capacity');
    $maxAtmosphericEfficiency = data_get($performance, 'max_supported_atmospheric_efficiency');
    $minHealthThrustMultiplier = data_get($performance, 'min_health_thrust_multiplier');

    $fuel = data_get($thruster, 'fuel', []);
    $burnRatePer10kNewton = data_get($fuel, 'burn_rate_per_10k_newton');

    $backwash = data_get($thruster, 'backwash', []);
    $backwashEnabled = data_get($backwash, 'enabled');
    $backwashAutomateSize = data_get($backwash, 'automate_size');
    $backwashMaxSpeed = data_get($backwash, 'max_speed');
    $backwashMaxDensity = data_get($backwash, 'max_density');
    $backwashMaxResistance = data_get($backwash, 'max_resistance');
    $backwashAfterburnerMultiplier = data_get($backwash, 'afterburner_multiplier');

    $handling = data_get($thruster, 'handling', []);
    $strengthSmoothing = data_get($handling, 'strength_smoothing');

    $sections = [];

    // Info
    $infoRows = array_values(array_filter([
        $role !== null ? ['label' => 'Role', 'value' => $role] : null,
        $vtolOnly !== null ? ['label' => 'VTOL Only', 'value' => $vtolOnly ? 'Yes' : 'No'] : null,
        $thrustCapacity !== null ? ['label' => 'Thrust Capacity', 'value' => Format::valueWithUnit($thrustCapacity, 'N', 0, true)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    // Performance
    $perfRows = array_values(array_filter([
        $maxAtmosphericEfficiency !== null ? ['label' => 'Max Atmospheric Efficiency', 'value' => Format::valueWithUnit($maxAtmosphericEfficiency, '', 2)] : null,
        $minHealthThrustMultiplier !== null ? ['label' => 'Min Health Thrust Multiplier', 'value' => Format::valueWithUnit($minHealthThrustMultiplier, '', 2)] : null,
        $burnRatePer10kNewton !== null ? ['label' => 'Burn Rate per 10k N', 'value' => Format::valueWithUnit($burnRatePer10kNewton, '', 3)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($perfRows !== []) {
        $sections[] = ['title' => 'Performance', 'rows' => $perfRows];
    }

    // Backwash
    $backwashRows = array_values(array_filter([
        $backwashEnabled !== null ? ['label' => 'Enabled', 'value' => $backwashEnabled ? 'Yes' : 'No'] : null,
        $backwashAutomateSize !== null ? ['label' => 'Automate Size', 'value' => $backwashAutomateSize ? 'Yes' : 'No'] : null,
        $backwashMaxSpeed !== null ? ['label' => 'Max Speed', 'value' => Format::valueWithUnit($backwashMaxSpeed, 'm/s', 2)] : null,
        $backwashMaxDensity !== null ? ['label' => 'Max Density', 'value' => Format::valueWithUnit($backwashMaxDensity, '', 2)] : null,
        $backwashMaxResistance !== null ? ['label' => 'Max Resistance', 'value' => Format::valueWithUnit($backwashMaxResistance, '', 2)] : null,
        $backwashAfterburnerMultiplier !== null ? ['label' => 'Afterburner Multiplier', 'value' => Format::valueWithUnit($backwashAfterburnerMultiplier, '', 2)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($backwashRows !== []) {
        $sections[] = ['title' => 'Backwash', 'rows' => $backwashRows];
    }

    // Handling
    $handlingRows = array_values(array_filter([
        $strengthSmoothing !== null ? ['label' => 'Strength Smoothing', 'value' => Format::valueWithUnit($strengthSmoothing, '', 2)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($handlingRows !== []) {
        $sections[] = ['title' => 'Handling', 'rows' => $handlingRows];
    }
@endphp

<x-data-card title="Thruster" :sections="$sections" />
