@use('App\Support\Format')
@props([
    'tractorBeam',
])

@php
    $forceMin = data_get($tractorBeam, 'force.min');
    $forceMax = data_get($tractorBeam, 'force.max');

    $rangeMin = data_get($tractorBeam, 'range.min');
    $rangeMax = data_get($tractorBeam, 'range.max');

    $sections = [];

    // Info
    $infoRows = array_values(array_filter([
        ($forceMin !== null || $forceMax !== null) ? ['label' => 'Force', 'value' => Format::range($forceMin, $forceMax, 'N', 0)] : null,
        ($rangeMin !== null || $rangeMax !== null) ? ['label' => 'Range', 'value' => Format::range($rangeMin, $rangeMax, 'm', 1)] : null,
        data_get($tractorBeam, 'force.max_volume') !== null ? ['label' => 'Max Volume', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'force.max_volume'), 'µSCU', 0)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    // Towing
    $towingRows = array_values(array_filter([
        data_get($tractorBeam, 'towing.force') !== null ? ['label' => 'Force', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'towing.force'), 'N', 1)] : null,
        data_get($tractorBeam, 'towing.max_acceleration') !== null ? ['label' => 'Max Acceleration', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'towing.max_acceleration'), 'm/s²', 1)] : null,
        data_get($tractorBeam, 'towing.max_distance') !== null ? ['label' => 'Max Distance', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'towing.max_distance'), 'm', 1)] : null,
        data_get($tractorBeam, 'towing.qt_mass_limit') !== null ? ['label' => 'QT Mass Limit', 'value' => Format::numberOrDash(data_get($tractorBeam, 'towing.qt_mass_limit'), 1)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($towingRows !== []) {
        $sections[] = ['title' => 'Towing', 'rows' => $towingRows];
    }

    // Additional Specifications
    $additionalRows = array_values(array_filter([
        data_get($tractorBeam, 'force.volume_force_coefficient') !== null ? ['label' => 'Volume Force Coefficient', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'force.volume_force_coefficient'), '', 2)] : null,
        data_get($tractorBeam, 'range.full_strength_distance') !== null ? ['label' => 'Full Strength Distance', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'range.full_strength_distance'), 'm', 2)] : null,
        data_get($tractorBeam, 'range.max_angle') !== null ? ['label' => 'Max Angle', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'range.max_angle'), '°', 2)] : null,
        data_get($tractorBeam, 'range.hit_radius') !== null ? ['label' => 'Hit Radius', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'range.hit_radius'), 'm', 2)] : null,
        data_get($tractorBeam, 'tether.tether_break_time') !== null ? ['label' => 'Tether Break Time', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'tether.tether_break_time'), 's', 2)] : null,
        data_get($tractorBeam, 'tether.safe_range_value_factor') !== null ? ['label' => 'Safe Range Factor', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'tether.safe_range_value_factor'), '', 2)] : null,
        data_get($tractorBeam, 'tether.allow_scrolling_into_breaking_range') !== null ? ['label' => 'Allow Scrolling Into Breaking Range', 'value' => data_get($tractorBeam, 'tether.allow_scrolling_into_breaking_range') ? 'Yes' : 'No'] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($additionalRows !== []) {
        $sections[] = ['title' => 'Additional Specifications', 'rows' => $additionalRows];
    }

    // Cargo Mode Overrides
    $cargoRows = array_values(array_filter([
        data_get($tractorBeam, 'cargo_mode_override.min_force') !== null ? ['label' => 'Min Force', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.min_force'), 'N', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.max_force') !== null ? ['label' => 'Max Force', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.max_force'), 'N', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.min_acceleration') !== null ? ['label' => 'Min Acceleration', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.min_acceleration'), 'm/s²', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.max_acceleration') !== null ? ['label' => 'Max Acceleration', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.max_acceleration'), 'm/s²', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.min_speed') !== null ? ['label' => 'Min Speed', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.min_speed'), 'm/s', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.max_speed') !== null ? ['label' => 'Max Speed', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.max_speed'), 'm/s', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.acceleration_factor') !== null ? ['label' => 'Acceleration Factor', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.acceleration_factor'), '', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.degrees_per_action') !== null ? ['label' => 'Degrees Per Action', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.degrees_per_action'), '°', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.max_angular_acceleration') !== null ? ['label' => 'Max Angular Acceleration', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.max_angular_acceleration'), 'deg/s²', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.max_angular_velocity') !== null ? ['label' => 'Max Angular Velocity', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.max_angular_velocity'), 'deg/s', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.degrees_per_action_scroll_wheel') !== null ? ['label' => 'Degrees Per Action Scroll Wheel', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.degrees_per_action_scroll_wheel'), '°', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.force_fraction_rotation') !== null ? ['label' => 'Force Fraction Rotation', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.force_fraction_rotation'), '', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.min_distance') !== null ? ['label' => 'Min Distance', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.min_distance'), 'm', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.max_distance') !== null ? ['label' => 'Max Distance', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.max_distance'), 'm', 2)] : null,
        data_get($tractorBeam, 'cargo_mode_override.full_strength_distance') !== null ? ['label' => 'Full Strength Distance', 'value' => Format::valueWithUnit(data_get($tractorBeam, 'cargo_mode_override.full_strength_distance'), 'm', 2)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($cargoRows !== []) {
        $sections[] = ['title' => 'Cargo Mode Overrides', 'rows' => $cargoRows];
    }
@endphp

<x-data-card title="Tractor Beam" :sections="$sections" />
