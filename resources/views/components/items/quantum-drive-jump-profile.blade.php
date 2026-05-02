@use('App\Support\Format')
@props([
    'profile' => [],
])

@php
    $metrics = array_values(array_filter([
        ['label' => 'Drive Speed', 'value' => data_get($profile, 'drive_speed'), 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Cooldown Time', 'value' => data_get($profile, 'cooldown_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Stage One', 'value' => data_get($profile, 'stage_one_accel_rate'), 'unit' => 'm/s²', 'precision' => 0],
        ['label' => 'Stage Two', 'value' => data_get($profile, 'stage_two_accel_rate'), 'unit' => 'm/s²', 'precision' => 0],
        ['label' => 'Engage Speed', 'value' => data_get($profile, 'engage_speed'), 'type' => 'int_unit', 'unit' => 'm/s'],
        ['label' => 'Interdiction Effect Time', 'value' => data_get($profile, 'interdiction_effect_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Calibration Rate', 'value' => data_get($profile, 'calibration_rate'), 'type' => 'int'],
        ['label' => 'Calibration Requirement', 'value' => data_get($profile, 'min_calibration_requirement'), 'type' => 'range', 'max' => data_get($profile, 'max_calibration_requirement'), 'unit' => '', 'precision' => 0],
        ['label' => 'Calibration Angle', 'value' => data_get($profile, 'calibration_process_angle_limit'), 'type' => 'range', 'max' => data_get($profile, 'calibration_warning_angle_limit'), 'unit' => 'deg', 'precision' => 1],
        ['label' => 'Calibration Delay', 'value' => data_get($profile, 'calibration_delay_in_seconds'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Spool Up Time', 'value' => data_get($profile, 'spool_up_time'), 'unit' => 's', 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));
@endphp

@foreach ($metrics as $metric)
    <x-dt-dd :label="$metric['label']">
        @switch($metric['type'] ?? 'unit')
            @case('int_unit')
                {{ (int) $metric['value'] }} {{ $metric['unit'] }}
                @break

            @case('int')
                {{ (int) $metric['value'] }}
                @break

            @case('range')
                {{ Format::range($metric['value'], $metric['max'], $metric['unit'], $metric['precision'] ?? 0) }}
                @break

            @default
                {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision'] ?? 0) }}
        @endswitch
    </x-dt-dd>
@endforeach
