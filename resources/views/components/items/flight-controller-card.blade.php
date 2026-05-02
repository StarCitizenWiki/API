@use('App\Support\Format')
@props([
    'flightController',
])

@php
    $primaryMetrics = array_values(array_filter([
        ['label' => 'SCM Speed', 'value' => data_get($flightController, 'scm_speed'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Boost Forward', 'value' => data_get($flightController, 'boost_speed_forward'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Max Speed', 'value' => data_get($flightController, 'max_speed'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Boost Backward', 'value' => data_get($flightController, 'boost_speed_backward'), 'unit' => 'm/s', 'precision' => 0],
    ], static fn (array $metric): bool => $metric['value'] !== null));

    $agilityMetrics = array_values(array_filter([
        ['label' => 'Pitch', 'value' => data_get($flightController, 'pitch'), 'boosted' => data_get($flightController, 'pitch_boosted'), 'unit' => '°/s', 'precision' => 1],
        ['label' => 'Yaw', 'value' => data_get($flightController, 'yaw'), 'boosted' => data_get($flightController, 'yaw_boosted'), 'unit' => '°/s', 'precision' => 1],
        ['label' => 'Roll', 'value' => data_get($flightController, 'roll'), 'boosted' => data_get($flightController, 'roll_boosted'), 'unit' => '°/s', 'precision' => 1],
    ], static fn (array $metric): bool => $metric['value'] !== null || $metric['boosted'] !== null));

    $boostSpeedMetrics = array_values(array_filter([
        ['label' => 'Forward', 'value' => data_get($flightController, 'boost_speed_forward'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Backward', 'value' => data_get($flightController, 'boost_speed_backward'), 'unit' => 'm/s', 'precision' => 0],
    ], static fn (array $metric): bool => $metric['value'] !== null));

    $boostCapacitor = data_get($flightController, 'boost_capacitor', []);
    $capacitorMetrics = array_values(array_filter([
        ['label' => 'Regen Time', 'value' => data_get($boostCapacitor, 'regen_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Regen / sec', 'value' => data_get($boostCapacitor, 'regen_per_sec'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Regen Delay', 'value' => data_get($boostCapacitor, 'regen_delay'), 'unit' => 's', 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));

    $boostActivation = data_get($flightController, 'boost_activation', []);
    $activationMetrics = array_values(array_filter([
        ['label' => 'Pre Delay Time', 'value' => data_get($boostActivation, 'pre_delay_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Ramp Up Time', 'value' => data_get($boostActivation, 'ramp_up_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Ramp Down Time', 'value' => data_get($boostActivation, 'ramp_down_time'), 'unit' => 's', 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));

    $thrusterDecay = data_get($flightController, 'thruster_decay', []);
    $thrusterDecayMetrics = array_values(array_filter([
        ['label' => 'Linear Acceleration', 'value' => data_get($thrusterDecay, 'linear_accel'), 'precision' => 1],
        ['label' => 'Angular Acceleration', 'value' => data_get($thrusterDecay, 'angular_accel'), 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));

    $multiplier = data_get($flightController, 'multiplier', []);
    $multiplierMetrics = array_values(array_filter([
        ['label' => 'Torque Imbalance', 'value' => data_get($multiplier, 'torque_imbalance'), 'precision' => 1],
        ['label' => 'Lift', 'value' => data_get($multiplier, 'lift'), 'precision' => 1],
        ['label' => 'Drag', 'value' => data_get($multiplier, 'drag'), 'precision' => 1],
        ['label' => 'SCM Max Drag', 'value' => data_get($multiplier, 'scm_max_drag'), 'precision' => 1],
        ['label' => 'Precision Landing', 'value' => data_get($multiplier, 'precision_landing'), 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));

    $precisionMode = data_get($flightController, 'precision_mode', []);
    $precisionModeMetrics = array_values(array_filter([
        ['label' => 'Proximity Assist', 'value' => data_get($precisionMode, 'max_speed_full_proximity_assist'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Zero Proximity Assist', 'value' => data_get($precisionMode, 'max_speed_zero_proximity_assist'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Min Distance', 'value' => data_get($precisionMode, 'min_distance'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Max Distance', 'value' => data_get($precisionMode, 'max_distance'), 'unit' => 'm', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));

    $collisionDetection = data_get($flightController, 'collision_detection', []);
    $collisionDetectionMetrics = array_values(array_filter([
        ['label' => 'Warn Speed', 'value' => data_get($collisionDetection, 'collision_warn_speed'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Warn Time', 'value' => data_get($collisionDetection, 'collision_warn_time'), 'unit' => 's', 'precision' => 0],
        ['label' => 'Danger Close Warn Time', 'value' => data_get($collisionDetection, 'collision_danger_close_warn_time'), 'unit' => 's', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));

    $gravlev = data_get($flightController, 'gravlev', []);
    $gravlevMetrics = array_values(array_filter([
        ['label' => 'Max Speed', 'value' => data_get($gravlev, 'max_speed'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Turn Friction', 'value' => data_get($gravlev, 'turn_friction'), 'precision' => 1],
        ['label' => 'Air Controller Multiplier', 'value' => data_get($gravlev, 'air_controller_multiplier'), 'precision' => 1],
        ['label' => 'Anti Fall Multiplier', 'value' => data_get($gravlev, 'anti_fall_multiplier'), 'precision' => 1],
        ['label' => 'Lateral Strafe Multiplier', 'value' => data_get($gravlev, 'lateral_strafe_multiplier'), 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));

    $recallParams = data_get($flightController, 'recall_params', []);
    $recallParamsMetrics = array_values(array_filter([
        ['label' => 'Hover Height', 'value' => data_get($recallParams, 'hover_height_at_destination'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Forward Offset', 'value' => data_get($recallParams, 'forward_offset'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Obstruction Detection Range', 'value' => data_get($recallParams, 'obstruction_detection_range'), 'unit' => 'm', 'precision' => 1],
        ['label' => 'Platform Detection Range', 'value' => data_get($recallParams, 'default_platform_detection_range'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Recall Distance', 'value' => data_get($recallParams, 'minimum_recall_distance'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Braking Distance Offset', 'value' => data_get($recallParams, 'braking_distance_offset'), 'unit' => 'm', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Flight Controller</h2>

        <x-dl-container>
            <x-slot:head>
                @foreach ($primaryMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-slot:head>

            <x-dl-section title="Agility">
                @foreach ($agilityMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        <span class="text-muted">
                            (boost {{ Format::valueWithUnit($metric['boosted'], $metric['unit'], $metric['precision']) }})
                        </span>
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Gravlev">
                @foreach ($gravlevMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        @if (isset($metric['unit']))
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Boost Capacitor">
                @foreach ($capacitorMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Boost Activation">
                @foreach ($activationMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Thruster Decay">
                @foreach ($thrusterDecayMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'] ?? '', $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Multipliers">
                @foreach ($multiplierMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        </x-dl-container>

        <x-dl-details title="Precision Mode / Collision Detection / Recall">
            <x-dl-section title="Precision Mode">
                @foreach ($precisionModeMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Collision Detection">
                @foreach ($collisionDetectionMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Recall">
                @foreach ($recallParamsMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        </x-dl-details>
    </div>
</div>
