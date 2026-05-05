@use('App\Support\Format')
@props([
    'tractorBeam',
])

@php
    $forceMin = data_get($tractorBeam, 'force.min');
    $forceMax = data_get($tractorBeam, 'force.max');

    $rangeMin = data_get($tractorBeam, 'range.min');
    $rangeMax = data_get($tractorBeam, 'range.max');

    $headMetrics = array_values(array_filter([
        ['label' => 'Max Volume', 'value' => data_get($tractorBeam, 'force.max_volume'), 'unit' => 'µSCU', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));

    $towingMetrics = array_values(array_filter([
        ['label' => 'Force', 'value' => data_get($tractorBeam, 'towing.force'), 'unit' => 'N', 'precision' => 1],
        ['label' => 'Max Acceleration', 'value' => data_get($tractorBeam, 'towing.max_acceleration'), 'unit' => 'm/s²', 'precision' => 1],
        ['label' => 'Max Distance', 'value' => data_get($tractorBeam, 'towing.max_distance'), 'unit' => 'm', 'precision' => 1],
        ['label' => 'QT Mass Limit', 'value' => data_get($tractorBeam, 'towing.qt_mass_limit'), 'unit' => '', 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));

    $additionalMetrics = array_values(array_filter([
        ['label' => 'Volume Force Coefficient', 'value' => data_get($tractorBeam, 'force.volume_force_coefficient'), 'unit' => '', 'precision' => 2],
        ['label' => 'Full Strength Distance', 'value' => data_get($tractorBeam, 'range.full_strength_distance'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Max Angle', 'value' => data_get($tractorBeam, 'range.max_angle'), 'unit' => '°', 'precision' => 2],
        ['label' => 'Hit Radius', 'value' => data_get($tractorBeam, 'range.hit_radius'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Tether Break Time', 'value' => data_get($tractorBeam, 'tether.tether_break_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Safe Range Factor', 'value' => data_get($tractorBeam, 'tether.safe_range_value_factor'), 'unit' => '', 'precision' => 2],
        ['label' => 'Allow Scrolling Into Breaking Range', 'value' => data_get($tractorBeam, 'tether.allow_scrolling_into_breaking_range'), 'unit' => '', 'precision' => 0, 'format' => 'boolean'],
    ], static fn (array $m): bool => $m['value'] !== null));

    $cargoMetrics = array_values(array_filter([
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
    ], static fn (array $m): bool => $m['value'] !== null));
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Tractor Beam</h2>

        <x-dl-container>
            <x-slot:head>
                @if ($forceMin !== null || $forceMax !== null)
                    <x-dt-dd label="Force">{{ Format::range($forceMin, $forceMax, 'N', 0) }}</x-dt-dd>
                @endif
                @if ($rangeMin !== null || $rangeMax !== null)
                    <x-dt-dd label="Range">{{ Format::range($rangeMin, $rangeMax, 'm', 1) }}</x-dt-dd>
                @endif
                @foreach ($headMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-slot:head>

            @if (count($towingMetrics) > 0)
                <x-dl-section title="Towing">
                    @foreach ($towingMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif

            @if (count($additionalMetrics) > 0 || count($cargoMetrics) > 0)
                <x-dl-details title="Additional Specifications">
                    @if (count($additionalMetrics) > 0)
                        <x-dl-section>
                            @foreach ($additionalMetrics as $metric)
                                <x-dt-dd :label="$metric['label']">
                                    @if (($metric['format'] ?? '') === 'boolean')
                                        {{ $metric['value'] ? 'Yes' : 'No' }}
                                    @else
                                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                                    @endif
                                </x-dt-dd>
                            @endforeach
                        </x-dl-section>
                    @endif

                    @if (count($cargoMetrics) > 0)
                        <x-dl-section title="Cargo Mode Overrides">
                            @foreach ($cargoMetrics as $metric)
                                <x-dt-dd :label="$metric['label']">
                                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                                </x-dt-dd>
                            @endforeach
                        </x-dl-section>
                    @endif
                </x-dl-details>
            @endif
        </x-dl-container>
    </div>
</div>
