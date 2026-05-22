@use('App\Support\Format')
@props([
    'turret',
])

@php
    $yawAxis = data_get($turret, 'yaw_axis', []);
    $pitchAxis = data_get($turret, 'pitch_axis', []);

    $sections = [];

    $primaryRows = array_values(array_filter([
        ['label' => 'Rotation Style', 'value' => data_get($turret, 'rotation_style')],
        data_get($turret, 'mounts') !== null
            ? ['label' => 'Mounts', 'value' => Format::numberOrDash(data_get($turret, 'mounts'))]
            : null,
        (data_get($turret, 'min_size') !== null || data_get($turret, 'max_size') !== null)
            ? ['label' => 'Equippable Size', 'value' => Format::range(data_get($turret, 'min_size'), data_get($turret, 'max_size'), '')]
            : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($primaryRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $primaryRows];
    }

    $performanceRows = array_values(array_filter([
        data_get($yawAxis, 'speed') !== null
            ? ['label' => 'Yaw Speed', 'value' => Format::valueWithUnit(data_get($yawAxis, 'speed'), 'deg/s', 2)]
            : null,
        data_get($yawAxis, 'time_to_full_speed') !== null
            ? ['label' => 'Yaw Time to Full Speed', 'value' => Format::valueWithUnit(data_get($yawAxis, 'time_to_full_speed'), 's', 2)]
            : null,
        data_get($pitchAxis, 'speed') !== null
            ? ['label' => 'Pitch Speed', 'value' => Format::valueWithUnit(data_get($pitchAxis, 'speed'), 'deg/s', 2)]
            : null,
        data_get($pitchAxis, 'time_to_full_speed') !== null
            ? ['label' => 'Pitch Time to Full Speed', 'value' => Format::valueWithUnit(data_get($pitchAxis, 'time_to_full_speed'), 's', 2)]
            : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($performanceRows !== []) {
        $sections[] = ['title' => 'Performance', 'rows' => $performanceRows];
    }

    $advancedRows = array_values(array_filter([
        data_get($yawAxis, 'slaved_only') !== null
            ? ['label' => 'Yaw Slaved Only', 'value' => data_get($yawAxis, 'slaved_only') ? 'Yes' : 'No']
            : null,
        data_get($yawAxis, 'acceleration_decay') !== null
            ? ['label' => 'Yaw Acceleration Decay', 'value' => Format::numberOrDash(data_get($yawAxis, 'acceleration_decay'), 2)]
            : null,
        (data_get($yawAxis, 'angle_limit_min') !== null || data_get($yawAxis, 'angle_limit_max') !== null)
            ? ['label' => 'Yaw Angle Limit', 'value' => Format::range(data_get($yawAxis, 'angle_limit_min'), data_get($yawAxis, 'angle_limit_max'), 'deg', 2)]
            : null,
        data_get($pitchAxis, 'slaved_only') !== null
            ? ['label' => 'Pitch Slaved Only', 'value' => data_get($pitchAxis, 'slaved_only') ? 'Yes' : 'No']
            : null,
        data_get($pitchAxis, 'acceleration_decay') !== null
            ? ['label' => 'Pitch Acceleration Decay', 'value' => Format::numberOrDash(data_get($pitchAxis, 'acceleration_decay'), 2)]
            : null,
        (data_get($pitchAxis, 'angle_limit_min') !== null || data_get($pitchAxis, 'angle_limit_max') !== null)
            ? ['label' => 'Pitch Angle Limit', 'value' => Format::range(data_get($pitchAxis, 'angle_limit_min'), data_get($pitchAxis, 'angle_limit_max'), 'deg', 2)]
            : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($advancedRows !== []) {
        $sections[] = ['title' => 'Advanced', 'rows' => $advancedRows];
    }
@endphp

<x-data-card title="Turret" :sections="$sections" {{ $attributes }} />
