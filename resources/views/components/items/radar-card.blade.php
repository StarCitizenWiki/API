@use('App\Support\Format')
@props([
    'radar',
 ])

@php
    $primaryMetrics = [
        ['label' => 'Cooldown', 'value' => data_get($radar, 'cooldown'), 'unit' => 's', 'precision' => 2],
    ];

    $sensitivity = data_get($radar, 'sensitivity', []);
    $sensitivityMetrics = [
        ['label' => 'Infrared', 'value' => data_get($sensitivity, 'infrared')],
        ['label' => 'Cross Section', 'value' => data_get($sensitivity, 'cross_section')],
        ['label' => 'Electromagnetic', 'value' => data_get($sensitivity, 'electromagnetic')],
        ['label' => 'Resource', 'value' => data_get($sensitivity, 'resource')],
        ['label' => 'dB', 'value' => data_get($sensitivity, 'db')],
    ];

    $groundVehicleSensitivity = data_get($radar, 'ground_vehicle_sensitivity', []);
    $groundVehicleSensitivityMetrics = [
        ['label' => 'Infrared', 'value' => data_get($groundVehicleSensitivity, 'infrared')],
        ['label' => 'Cross Section', 'value' => data_get($groundVehicleSensitivity, 'cross_section')],
        ['label' => 'Electromagnetic', 'value' => data_get($groundVehicleSensitivity, 'electromagnetic')],
        ['label' => 'Resource', 'value' => data_get($groundVehicleSensitivity, 'resource')],
        ['label' => 'dB', 'value' => data_get($groundVehicleSensitivity, 'db')],
    ];

    $piercing = data_get($radar, 'piercing', []);
    $piercingMetrics = [
        ['label' => 'Infrared', 'value' => data_get($piercing, 'infrared')],
        ['label' => 'Cross Section', 'value' => data_get($piercing, 'cross_section')],
        ['label' => 'Electromagnetic', 'value' => data_get($piercing, 'electromagnetic')],
        ['label' => 'Resource', 'value' => data_get($piercing, 'resource')],
        ['label' => 'dB', 'value' => data_get($piercing, 'db')],
    ];

    $aimAssist = data_get($radar, 'aim_assist', []);
    $aimAssistMetrics = [
        ['label' => 'Min Assignment Distance', 'value' => data_get($aimAssist, 'distance_min_assignment'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Max Assignment Distance', 'value' => data_get($aimAssist, 'distance_max_assignment'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Outside Range Buffer', 'value' => data_get($aimAssist, 'outside_range_buffer_distance'), 'unit' => 'm', 'precision' => 0],
    ];

@endphp

<x-item-card title="Radar">
    <x-dl-container>
        <x-slot:head>
            @foreach ($primaryMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-slot:head>

        <x-dl-section title="Sensitivity">
            @foreach ($sensitivityMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::numberOrDash($metric['value'], 2) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Ground Vehicle Sensitivity">
            @foreach ($groundVehicleSensitivityMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::numberOrDash($metric['value'], 2) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Aim Assist">
            @foreach ($aimAssistMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>

    <x-dl-details title="Piercing">
        <x-dl-section title="Piercing">
            @foreach ($piercingMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::numberOrDash($metric['value'], 2) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-details>
</x-item-card>
