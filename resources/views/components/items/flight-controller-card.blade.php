@use('App\Support\Format')
@props([
    'flightController',
])

@php
    $sections = [];

    $speedRows = array_values(array_filter([
        data_get($flightController, 'scm_speed') !== null
            ? ['label' => 'SCM Speed', 'value' => Format::valueWithUnit(data_get($flightController, 'scm_speed'), 'm/s', 0)] : null,
        data_get($flightController, 'max_speed') !== null
            ? ['label' => 'Max Speed', 'value' => Format::valueWithUnit(data_get($flightController, 'max_speed'), 'm/s', 0)] : null,
        data_get($flightController, 'boost_speed_forward') !== null
            ? ['label' => 'Boost Forward', 'value' => Format::valueWithUnit(data_get($flightController, 'boost_speed_forward'), 'm/s', 0)] : null,
        data_get($flightController, 'boost_speed_backward') !== null
            ? ['label' => 'Boost Backward', 'value' => Format::valueWithUnit(data_get($flightController, 'boost_speed_backward'), 'm/s', 0)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($speedRows !== []) {
        $sections[] = ['title' => 'Speed', 'rows' => $speedRows];
    }

    $agilityRows = array_values(array_filter([
        data_get($flightController, 'pitch') !== null
            ? ['label' => 'Pitch', 'value' => Format::valueWithUnit(data_get($flightController, 'pitch'), '°/s', 1) . ' (boost ' . Format::valueWithUnit(data_get($flightController, 'pitch_boosted'), '°/s', 1) . ')'] : null,
        data_get($flightController, 'yaw') !== null
            ? ['label' => 'Yaw', 'value' => Format::valueWithUnit(data_get($flightController, 'yaw'), '°/s', 1) . ' (boost ' . Format::valueWithUnit(data_get($flightController, 'yaw_boosted'), '°/s', 1) . ')'] : null,
        data_get($flightController, 'roll') !== null
            ? ['label' => 'Roll', 'value' => Format::valueWithUnit(data_get($flightController, 'roll'), '°/s', 1) . ' (boost ' . Format::valueWithUnit(data_get($flightController, 'roll_boosted'), '°/s', 1) . ')'] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($agilityRows !== []) {
        $sections[] = ['title' => 'Agility', 'rows' => $agilityRows];
    }

    $gravlev = data_get($flightController, 'gravlev', []);
    if (is_array($gravlev) && $gravlev !== []) {
        $gravlevRows = array_values(array_filter([
            data_get($gravlev, 'max_speed') !== null
                ? ['label' => 'Max Speed', 'value' => Format::valueWithUnit(data_get($gravlev, 'max_speed'), 'm/s', 0)] : null,
            data_get($gravlev, 'turn_friction') !== null
                ? ['label' => 'Turn Friction', 'value' => Format::numberOrDash(data_get($gravlev, 'turn_friction'), 1)] : null,
            data_get($gravlev, 'air_controller_multiplier') !== null
                ? ['label' => 'Air Controller Multiplier', 'value' => Format::numberOrDash(data_get($gravlev, 'air_controller_multiplier'), 1)] : null,
            data_get($gravlev, 'anti_fall_multiplier') !== null
                ? ['label' => 'Anti Fall Multiplier', 'value' => Format::numberOrDash(data_get($gravlev, 'anti_fall_multiplier'), 1)] : null,
            data_get($gravlev, 'lateral_strafe_multiplier') !== null
                ? ['label' => 'Lateral Strafe Multiplier', 'value' => Format::numberOrDash(data_get($gravlev, 'lateral_strafe_multiplier'), 1)] : null,
        ], static fn (?array $m): bool => $m !== null));

        if ($gravlevRows !== []) {
            $sections[] = ['title' => 'Gravlev', 'rows' => $gravlevRows];
        }
    }

    $boostCapacitor = data_get($flightController, 'boost_capacitor', []);
    $capacitorRows = array_values(array_filter([
        data_get($boostCapacitor, 'regen_time') !== null
            ? ['label' => 'Regen Time', 'value' => Format::valueWithUnit(data_get($boostCapacitor, 'regen_time'), 's', 1)] : null,
        data_get($boostCapacitor, 'regen_per_sec') !== null
            ? ['label' => 'Regen / sec', 'value' => Format::valueWithUnit(data_get($boostCapacitor, 'regen_per_sec'), '/s', 2)] : null,
        data_get($boostCapacitor, 'regen_delay') !== null
            ? ['label' => 'Regen Delay', 'value' => Format::valueWithUnit(data_get($boostCapacitor, 'regen_delay'), 's', 1)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($capacitorRows !== []) {
        $sections[] = ['title' => 'Boost Capacitor', 'rows' => $capacitorRows];
    }

    $boostActivation = data_get($flightController, 'boost_activation', []);
    $activationRows = array_values(array_filter([
        data_get($boostActivation, 'pre_delay_time') !== null
            ? ['label' => 'Pre Delay Time', 'value' => Format::valueWithUnit(data_get($boostActivation, 'pre_delay_time'), 's', 1)] : null,
        data_get($boostActivation, 'ramp_up_time') !== null
            ? ['label' => 'Ramp Up Time', 'value' => Format::valueWithUnit(data_get($boostActivation, 'ramp_up_time'), 's', 1)] : null,
        data_get($boostActivation, 'ramp_down_time') !== null
            ? ['label' => 'Ramp Down Time', 'value' => Format::valueWithUnit(data_get($boostActivation, 'ramp_down_time'), 's', 1)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($activationRows !== []) {
        $sections[] = ['title' => 'Boost Activation', 'rows' => $activationRows];
    }

    $thrusterDecay = data_get($flightController, 'thruster_decay', []);
    $decayRows = array_values(array_filter([
        data_get($thrusterDecay, 'linear_accel') !== null
            ? ['label' => 'Linear Acceleration', 'value' => Format::numberOrDash(data_get($thrusterDecay, 'linear_accel'), 1)] : null,
        data_get($thrusterDecay, 'angular_accel') !== null
            ? ['label' => 'Angular Acceleration', 'value' => Format::numberOrDash(data_get($thrusterDecay, 'angular_accel'), 1)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($decayRows !== []) {
        $sections[] = ['title' => 'Thruster Decay', 'rows' => $decayRows];
    }

    $multiplier = data_get($flightController, 'multiplier', []);
    $multiplierRows = array_values(array_filter([
        data_get($multiplier, 'torque_imbalance') !== null
            ? ['label' => 'Torque Imbalance', 'value' => Format::numberOrDash(data_get($multiplier, 'torque_imbalance'), 1)] : null,
        data_get($multiplier, 'lift') !== null
            ? ['label' => 'Lift', 'value' => Format::numberOrDash(data_get($multiplier, 'lift'), 1)] : null,
        data_get($multiplier, 'drag') !== null
            ? ['label' => 'Drag', 'value' => Format::numberOrDash(data_get($multiplier, 'drag'), 1)] : null,
        data_get($multiplier, 'scm_max_drag') !== null
            ? ['label' => 'SCM Max Drag', 'value' => Format::numberOrDash(data_get($multiplier, 'scm_max_drag'), 1)] : null,
        data_get($multiplier, 'precision_landing') !== null
            ? ['label' => 'Precision Landing', 'value' => Format::numberOrDash(data_get($multiplier, 'precision_landing'), 1)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($multiplierRows !== []) {
        $sections[] = ['title' => 'Multipliers', 'rows' => $multiplierRows];
    }

    $precisionMode = data_get($flightController, 'precision_mode', []);
    $precisionRows = array_values(array_filter([
        data_get($precisionMode, 'max_speed_full_proximity_assist') !== null
            ? ['label' => 'Proximity Assist', 'value' => Format::valueWithUnit(data_get($precisionMode, 'max_speed_full_proximity_assist'), 'm/s', 0)] : null,
        data_get($precisionMode, 'max_speed_zero_proximity_assist') !== null
            ? ['label' => 'Zero Proximity Assist', 'value' => Format::valueWithUnit(data_get($precisionMode, 'max_speed_zero_proximity_assist'), 'm/s', 0)] : null,
        data_get($precisionMode, 'min_distance') !== null
            ? ['label' => 'Min Distance', 'value' => Format::valueWithUnit(data_get($precisionMode, 'min_distance'), 'm', 0)] : null,
        data_get($precisionMode, 'max_distance') !== null
            ? ['label' => 'Max Distance', 'value' => Format::valueWithUnit(data_get($precisionMode, 'max_distance'), 'm', 0)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($precisionRows !== []) {
        $sections[] = ['title' => 'Precision Mode', 'rows' => $precisionRows];
    }

    $collisionDetection = data_get($flightController, 'collision_detection', []);
    $collisionRows = array_values(array_filter([
        data_get($collisionDetection, 'collision_warn_speed') !== null
            ? ['label' => 'Warn Speed', 'value' => Format::valueWithUnit(data_get($collisionDetection, 'collision_warn_speed'), 'm/s', 0)] : null,
        data_get($collisionDetection, 'collision_warn_time') !== null
            ? ['label' => 'Warn Time', 'value' => Format::valueWithUnit(data_get($collisionDetection, 'collision_warn_time'), 's', 0)] : null,
        data_get($collisionDetection, 'collision_danger_close_warn_time') !== null
            ? ['label' => 'Danger Close Warn Time', 'value' => Format::valueWithUnit(data_get($collisionDetection, 'collision_danger_close_warn_time'), 's', 0)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($collisionRows !== []) {
        $sections[] = ['title' => 'Collision Detection', 'rows' => $collisionRows];
    }

    $recallParams = data_get($flightController, 'recall_params', []);
    $recallRows = array_values(array_filter([
        data_get($recallParams, 'hover_height_at_destination') !== null
            ? ['label' => 'Hover Height', 'value' => Format::valueWithUnit(data_get($recallParams, 'hover_height_at_destination'), 'm', 0)] : null,
        data_get($recallParams, 'forward_offset') !== null
            ? ['label' => 'Forward Offset', 'value' => Format::valueWithUnit(data_get($recallParams, 'forward_offset'), 'm', 0)] : null,
        data_get($recallParams, 'obstruction_detection_range') !== null
            ? ['label' => 'Obstruction Detection Range', 'value' => Format::valueWithUnit(data_get($recallParams, 'obstruction_detection_range'), 'm', 1)] : null,
        data_get($recallParams, 'default_platform_detection_range') !== null
            ? ['label' => 'Platform Detection Range', 'value' => Format::valueWithUnit(data_get($recallParams, 'default_platform_detection_range'), 'm', 0)] : null,
        data_get($recallParams, 'minimum_recall_distance') !== null
            ? ['label' => 'Recall Distance', 'value' => Format::valueWithUnit(data_get($recallParams, 'minimum_recall_distance'), 'm', 0)] : null,
        data_get($recallParams, 'braking_distance_offset') !== null
            ? ['label' => 'Braking Distance Offset', 'value' => Format::valueWithUnit(data_get($recallParams, 'braking_distance_offset'), 'm', 0)] : null,
    ], static fn (?array $m): bool => $m !== null));

    if ($recallRows !== []) {
        $sections[] = ['title' => 'Recall', 'rows' => $recallRows];
    }
@endphp

<x-data-card title="Flight Controller" :sections="$sections" {{ $attributes }} />
