@use('App\Support\Format')
@props([
    'seat',
])

@php
    $seatType = data_get($seat, 'seat_type');
    $ejection = data_get($seat, 'ejection', []);
    $setYawPitchLimits = data_get($seat, 'set_yaw_pitch_limits');

    $yaw = data_get($seat, 'yaw', []);
    $yawMin = data_get($yaw, 'minimum');
    $yawMax = data_get($yaw, 'maximum');

    $pitch = data_get($seat, 'pitch', []);
    $pitchMin = data_get($pitch, 'minimum');
    $pitchMax = data_get($pitch, 'maximum');

    $sections = [];

    $infoRows = array_values(array_filter([
        ['label' => 'Seat Type', 'value' => $seatType ?? '-'],
        ['label' => 'Has Ejection', 'value' => is_array($ejection) && $ejection !== [] ? 'Yes' : 'No'],
        data_get($ejection, 'ejection_loop_time') !== null
            ? ['label' => 'Ejection Time', 'value' => Format::valueWithUnit(data_get($ejection, 'ejection_loop_time'), 's', 2)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    $axisRows = array_values(array_filter([
        ['label' => 'Set Yaw/Pitch Limits', 'value' => $setYawPitchLimits ? 'Yes' : 'No'],
        $yawMin !== null || $yawMax !== null
            ? ['label' => 'Yaw', 'value' => Format::range($yawMin, $yawMax, 'deg', 2)] : null,
        $pitchMin !== null || $pitchMax !== null
            ? ['label' => 'Pitch', 'value' => Format::range($pitchMin, $pitchMax, 'deg', 2)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($axisRows !== []) {
        $sections[] = ['title' => 'Axis Limits', 'rows' => $axisRows];
    }

    $ejectionRows = array_values(array_filter([
        data_get($ejection, 'max_linear_velocity') !== null
            ? ['label' => 'Max Linear Velocity', 'value' => Format::valueWithUnit(data_get($ejection, 'max_linear_velocity'), 'm/s', 2)] : null,
        data_get($ejection, 'max_linear_acceleration') !== null
            ? ['label' => 'Max Linear Acceleration', 'value' => Format::valueWithUnit(data_get($ejection, 'max_linear_acceleration'), 'm/s²', 2)] : null,
        data_get($ejection, 'max_angular_velocity') !== null
            ? ['label' => 'Max Angular Velocity', 'value' => Format::valueWithUnit(data_get($ejection, 'max_angular_velocity'), 'rad/s', 2)] : null,
        data_get($ejection, 'max_angular_acceleration') !== null
            ? ['label' => 'Max Angular Acceleration', 'value' => Format::valueWithUnit(data_get($ejection, 'max_angular_acceleration'), 'rad/s²', 2)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($ejectionRows !== []) {
        $sections[] = ['title' => 'Ejection', 'rows' => $ejectionRows];
    }
@endphp

<x-data-card title="Seat" :sections="$sections" {{ $attributes }} />
