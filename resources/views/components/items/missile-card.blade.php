@use('App\Support\Format')
@php use Illuminate\Support\Str; @endphp
@props([
    'missile',
])

@php
    $signalType = data_get($missile, 'signal_type');
    $trackingSignalMin = data_get($missile, 'tracking_signal_min');
    $clusterSize = data_get($missile, 'cluster_size');
    $damageTotal = data_get($missile, 'damage_total');
    $damageMap = data_get($missile, 'damage_map', []);

    $flight = data_get($missile, 'flight', []);
    $targetLock = data_get($missile, 'target_lock', []);
    $explosion = data_get($missile, 'explosion', []);
    $delays = data_get($missile, 'delays', []);

    $sections = [];

    // Info
    $infoRows = array_values(array_filter([
        ['label' => 'Signal Type', 'value' => $signalType],
        ['label' => 'Lock Range', 'value' => (data_get($targetLock, 'range_min') ?? data_get($targetLock, 'range_max')) !== null ? Format::range(data_get($targetLock, 'range_min'), data_get($targetLock, 'range_max'), 'm') : null],
        ['label' => 'Damage Total', 'value' => $damageTotal !== null ? Format::numberOrDash($damageTotal, 2) : null],
        ['label' => 'Range', 'value' => data_get($flight, 'range') !== null ? Format::valueWithUnit(data_get($flight, 'range'), 'm', 0) : null],
        ['label' => 'Arm Time', 'value' => data_get($delays, 'arm_time') !== null ? Format::valueWithUnit(data_get($delays, 'arm_time'), 's', 1) : null],
        ['label' => 'Cluster Size', 'value' => $clusterSize !== null ? Format::numberOrDash($clusterSize, 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    // Target Lock
    $targetLockRows = array_values(array_filter([
        ['label' => 'Lock Angle', 'value' => data_get($targetLock, 'angle') !== null ? Format::valueWithUnit(data_get($targetLock, 'angle'), 'deg', 1) : null],
        ['label' => 'Tracking Signal Min', 'value' => $trackingSignalMin !== null ? Format::numberOrDash($trackingSignalMin, 2) : null],
        ['label' => 'Signal Resilience Min', 'value' => data_get($targetLock, 'signal_resilience_min') !== null ? Format::numberOrDash(data_get($targetLock, 'signal_resilience_min'), 2) : null],
        ['label' => 'Signal Resilience Max', 'value' => data_get($targetLock, 'signal_resilience_max') !== null ? Format::numberOrDash(data_get($targetLock, 'signal_resilience_max'), 2) : null],
        ['label' => 'Signal Amplifier', 'value' => data_get($targetLock, 'signal_amplifier') !== null ? Format::numberOrDash(data_get($targetLock, 'signal_amplifier'), 2) : null],
        ['label' => 'Lock Increase Rate', 'value' => data_get($targetLock, 'increase_rate') !== null ? Format::valueWithUnit(data_get($targetLock, 'increase_rate'), '/s', 2) : null],
        ['label' => 'Allow Dumb Firing', 'value' => data_get($targetLock, 'allow_dumb_firing') !== null ? (data_get($targetLock, 'allow_dumb_firing') ? 'Yes' : 'No') : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($targetLockRows !== []) {
        $sections[] = ['title' => 'Target Lock', 'rows' => $targetLockRows];
    }

    // Damage
    $damageRows = array_values(array_filter(
        collect($damageMap)->map(fn (float|int|null $value, string $type): ?array => ($value !== null && $value > 0) ? ['label' => Str::headline($type), 'value' => Format::numberOrDash($value, 2)] : null)->values()->all(),
        static fn (?array $row): bool => $row !== null,
    ));

    if ($damageRows !== []) {
        $sections[] = ['title' => 'Damage', 'rows' => $damageRows];
    }

    // Flight Performance
    $flightRows = array_values(array_filter([
        ['label' => 'Speed', 'value' => data_get($flight, 'speed') !== null ? Format::valueWithUnit(data_get($flight, 'speed'), 'm/s', 2) : null],
        ['label' => 'Max Lifetime', 'value' => data_get($flight, 'max_lifetime') !== null ? Format::valueWithUnit(data_get($flight, 'max_lifetime'), 's', 2) : null],
        ['label' => 'Boost Speed', 'value' => data_get($flight, 'boost_speed') !== null ? Format::valueWithUnit(data_get($flight, 'boost_speed'), 'm/s', 2) : null],
        ['label' => 'Intercept Speed', 'value' => data_get($flight, 'intercept_speed') !== null ? Format::valueWithUnit(data_get($flight, 'intercept_speed'), 'm/s', 2) : null],
        ['label' => 'Terminal Speed', 'value' => data_get($flight, 'terminal_speed') !== null ? Format::valueWithUnit(data_get($flight, 'terminal_speed'), 'm/s', 2) : null],
        ['label' => 'Fuel Tank Size', 'value' => data_get($flight, 'fuel_tank_size') !== null ? Format::numberOrDash(data_get($flight, 'fuel_tank_size'), 0) : null],
        ['label' => 'Boost Phase Duration', 'value' => data_get($flight, 'boost_phase_duration') !== null ? Format::valueWithUnit(data_get($flight, 'boost_phase_duration'), 's', 2) : null],
        ['label' => 'Terminal Phase Engagement Time', 'value' => data_get($flight, 'terminal_phase_engagement_time') !== null ? Format::valueWithUnit(data_get($flight, 'terminal_phase_engagement_time'), 's', 2) : null],
        ['label' => 'Terminal Phase Engagement Angle', 'value' => data_get($flight, 'terminal_phase_engagement_angle') !== null ? Format::valueWithUnit(data_get($flight, 'terminal_phase_engagement_angle'), 'deg', 1) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($flightRows !== []) {
        $sections[] = ['title' => 'Flight Performance', 'rows' => $flightRows];
    }

    // Explosion
    $explosionRadiusMin = data_get($explosion, 'radius_min');
    $explosionRadiusMax = data_get($explosion, 'radius_max');
    $explosionRows = array_values(array_filter([
        ['label' => 'Radius', 'value' => ($explosionRadiusMin ?? $explosionRadiusMax) !== null ? Format::range($explosionRadiusMin, $explosionRadiusMax, 'm', 2) : null],
        ['label' => 'Is Cluster', 'value' => data_get($explosion, 'is_cluster') !== null ? (data_get($explosion, 'is_cluster') ? 'Yes' : 'No') : null],
        ['label' => 'Cluster Size', 'value' => data_get($explosion, 'cluster_size') !== null ? Format::numberOrDash(data_get($explosion, 'cluster_size'), 0) : null],
        ['label' => 'Requires Launcher', 'value' => data_get($explosion, 'requires_launcher') !== null ? (data_get($explosion, 'requires_launcher') ? 'Yes' : 'No') : null],
        ['label' => 'Safety Distance', 'value' => data_get($explosion, 'safety_distance') !== null ? Format::valueWithUnit(data_get($explosion, 'safety_distance'), 'm', 2) : null],
        ['label' => 'Proximity', 'value' => data_get($explosion, 'proximity') !== null ? Format::valueWithUnit(data_get($explosion, 'proximity'), 'm', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($explosionRows !== []) {
        $sections[] = ['title' => 'Explosion', 'rows' => $explosionRows];
    }

    // Delays
    $delaysRows = array_values(array_filter([
        ['label' => 'Ignite Time', 'value' => data_get($delays, 'ignite_time') !== null ? Format::valueWithUnit(data_get($delays, 'ignite_time'), 's', 2) : null],
        ['label' => 'Collision Delay Time', 'value' => data_get($delays, 'collision_delay_time') !== null ? Format::valueWithUnit(data_get($delays, 'collision_delay_time'), 's', 2) : null],
        ['label' => 'Lock Time', 'value' => data_get($delays, 'lock_time') !== null ? Format::valueWithUnit(data_get($delays, 'lock_time'), 's', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($delaysRows !== []) {
        $sections[] = ['title' => 'Delays', 'rows' => $delaysRows];
    }
@endphp

<x-data-card title="Missile" :sections="$sections" {{ $attributes }} />
