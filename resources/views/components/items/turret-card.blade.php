@use('App\Support\Format')
@props([
    'turret',
])

@php
    $yawAxis = data_get($turret, 'yaw_axis', []);
    $pitchAxis = data_get($turret, 'pitch_axis', []);

    $primaryMetrics = [
        ['label' => 'Rotation Style', 'value' => data_get($turret, 'rotation_style'), 'format' => 'text'],
        ['label' => 'Mounts', 'value' => data_get($turret, 'mounts'), 'format' => 'integer'],
    ];

    $hasEquippableSize = data_get($turret, 'min_size') !== null || data_get($turret, 'max_size') !== null;

    $performanceMetrics = [
        ['label' => 'Yaw Speed', 'value' => data_get($yawAxis, 'speed'), 'unit' => 'deg/s', 'precision' => 2],
        ['label' => 'Yaw Time to Full Speed', 'value' => data_get($yawAxis, 'time_to_full_speed'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Pitch Speed', 'value' => data_get($pitchAxis, 'speed'), 'unit' => 'deg/s', 'precision' => 2],
        ['label' => 'Pitch Time to Full Speed', 'value' => data_get($pitchAxis, 'time_to_full_speed'), 'unit' => 's', 'precision' => 2],
    ];

    $advancedMetrics = [
        ['label' => 'Yaw Slaved Only', 'value' => data_get($yawAxis, 'slaved_only'), 'type' => 'bool'],
        ['label' => 'Yaw Acceleration Decay', 'value' => data_get($yawAxis, 'acceleration_decay'), 'type' => 'numeric', 'precision' => 2],
        ['label' => 'Yaw Angle Limit', 'min' => data_get($yawAxis, 'angle_limit_min'), 'max' => data_get($yawAxis, 'angle_limit_max'), 'type' => 'range', 'unit' => 'deg', 'precision' => 2],
        ['label' => 'Pitch Slaved Only', 'value' => data_get($pitchAxis, 'slaved_only'), 'type' => 'bool'],
        ['label' => 'Pitch Acceleration Decay', 'value' => data_get($pitchAxis, 'acceleration_decay'), 'type' => 'numeric', 'precision' => 2],
        ['label' => 'Pitch Angle Limit', 'min' => data_get($pitchAxis, 'angle_limit_min'), 'max' => data_get($pitchAxis, 'angle_limit_max'), 'type' => 'range', 'unit' => 'deg', 'precision' => 2],
    ];

@endphp

<x-item-card title="Turret">
    <x-dl-container>
        <x-slot:head>
            @foreach ($primaryMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if ($metric['format'] === 'integer')
                        {{ Format::numberOrDash($metric['value']) }}
                    @else
                        {{ $metric['value'] }}
                    @endif
                </x-dt-dd>
            @endforeach
            <x-dt-dd label="Equippable Size" :value="$hasEquippableSize">{{ Format::range(data_get($turret, 'min_size'), data_get($turret, 'max_size'), '') }}</x-dt-dd>
        </x-slot:head>

        <x-dl-details title="Performance" :open="true">
            <x-dl-section>
                @foreach ($performanceMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        </x-dl-details>

        <x-dl-details title="Advanced">
            <x-dl-section>
                @foreach ($advancedMetrics as $metric)
                    <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                        @if ($metric['type'] === 'bool')
                            {{ $metric['value'] ? 'Yes' : 'No' }}
                        @elseif ($metric['type'] === 'range')
                            {{ Format::range($metric['min'], $metric['max'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        </x-dl-details>
    </x-dl-container>
</x-item-card>
