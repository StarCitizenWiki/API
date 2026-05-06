@use('App\Support\Format')
@props([
    'tractorBeam',
])

@php
    $forceMin = data_get($tractorBeam, 'force.min');
    $forceMax = data_get($tractorBeam, 'force.max');

    $rangeMin = data_get($tractorBeam, 'range.min');
    $rangeMax = data_get($tractorBeam, 'range.max');

    $headMetrics = [
        ['label' => 'Max Volume', 'value' => data_get($tractorBeam, 'force.max_volume'), 'unit' => 'µSCU', 'precision' => 0],
    ];

    $towingMetrics = [
        ['label' => 'Force', 'value' => data_get($tractorBeam, 'towing.force'), 'unit' => 'N', 'precision' => 1],
        ['label' => 'Max Acceleration', 'value' => data_get($tractorBeam, 'towing.max_acceleration'), 'unit' => 'm/s²', 'precision' => 1],
        ['label' => 'Max Distance', 'value' => data_get($tractorBeam, 'towing.max_distance'), 'unit' => 'm', 'precision' => 1],
        ['label' => 'QT Mass Limit', 'value' => data_get($tractorBeam, 'towing.qt_mass_limit'), 'unit' => '', 'precision' => 1],
    ];

    $additionalMetrics = [
        ['label' => 'Volume Force Coefficient', 'value' => data_get($tractorBeam, 'force.volume_force_coefficient'), 'unit' => '', 'precision' => 2],
        ['label' => 'Full Strength Distance', 'value' => data_get($tractorBeam, 'range.full_strength_distance'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Max Angle', 'value' => data_get($tractorBeam, 'range.max_angle'), 'unit' => '°', 'precision' => 2],
        ['label' => 'Hit Radius', 'value' => data_get($tractorBeam, 'range.hit_radius'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Tether Break Time', 'value' => data_get($tractorBeam, 'tether.tether_break_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Safe Range Factor', 'value' => data_get($tractorBeam, 'tether.safe_range_value_factor'), 'unit' => '', 'precision' => 2],
        ['label' => 'Allow Scrolling Into Breaking Range', 'value' => data_get($tractorBeam, 'tether.allow_scrolling_into_breaking_range'), 'unit' => '', 'precision' => 0, 'format' => 'boolean'],
    ];

    $cargoMetrics = [
        ['label' => 'Min Force', 'value' => data_get($tractorBeam, 'cargo_mode_override.min_force'), 'unit' => 'N', 'precision' => 2],
        ['label' => 'Max Force', 'value' => data_get($tractorBeam, 'cargo_mode_override.max_force'), 'unit' => 'N', 'precision' => 2],
        ['label' => 'Min Acceleration', 'value' => data_get($tractorBeam, 'cargo_mode_override.min_acceleration'), 'unit' => 'm/s²', 'precision' => 2],
        ['label' => 'Max Acceleration', 'value' => data_get($tractorBeam, 'cargo_mode_override.max_acceleration'), 'unit' => 'm/s²', 'precision' => 2],
        ['label' => 'Min Speed', 'value' => data_get($tractorBeam, 'cargo_mode_override.min_speed'), 'unit' => 'm/s', 'precision' => 2],
        ['label' => 'Max Speed', 'value' => data_get($tractorBeam, 'cargo_mode_override.max_speed'), 'unit' => 'm/s', 'precision' => 2],
        ['label' => 'Acceleration Factor', 'value' => data_get($tractorBeam, 'cargo_mode_override.acceleration_factor'), 'unit' => '', 'precision' => 2],
        ['label' => 'Degrees Per Action', 'value' => data_get($tractorBeam, 'cargo_mode_override.degrees_per_action'), 'unit' => '°', 'precision' => 2],
        ['label' => 'Max Angular Acceleration', 'value' => data_get($tractorBeam, 'cargo_mode_override.max_angular_acceleration'), 'unit' => 'deg/s²', 'precision' => 2],
        ['label' => 'Max Angular Velocity', 'value' => data_get($tractorBeam, 'cargo_mode_override.max_angular_velocity'), 'unit' => 'deg/s', 'precision' => 2],
        ['label' => 'Degrees Per Action Scroll Wheel', 'value' => data_get($tractorBeam, 'cargo_mode_override.degrees_per_action_scroll_wheel'), 'unit' => '°', 'precision' => 2],
        ['label' => 'Force Fraction Rotation', 'value' => data_get($tractorBeam, 'cargo_mode_override.force_fraction_rotation'), 'unit' => '', 'precision' => 2],
        ['label' => 'Min Distance', 'value' => data_get($tractorBeam, 'cargo_mode_override.min_distance'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Max Distance', 'value' => data_get($tractorBeam, 'cargo_mode_override.max_distance'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Full Strength Distance', 'value' => data_get($tractorBeam, 'cargo_mode_override.full_strength_distance'), 'unit' => 'm', 'precision' => 2],
    ];

@endphp

<x-item-card title="Tractor Beam">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Force" :value="$forceMin ?? $forceMax">{{ Format::range($forceMin, $forceMax, 'N', 0) }}</x-dt-dd>
            <x-dt-dd label="Range" :value="$rangeMin ?? $rangeMax">{{ Format::range($rangeMin, $rangeMax, 'm', 1) }}</x-dt-dd>
            @foreach ($headMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-slot:head>

        <x-dl-section title="Towing">
            @foreach ($towingMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-details title="Additional Specifications">
            <x-dl-section>
                @foreach ($additionalMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if (($metric['format'] ?? '') === 'boolean')
                            {{ $metric['value'] ? 'Yes' : 'No' }}
                        @else
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Cargo Mode Overrides">
                @foreach ($cargoMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        </x-dl-details>
    </x-dl-container>
</x-item-card>
