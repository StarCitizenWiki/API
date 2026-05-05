@use('App\Support\Format')
@props([
    'turret',
])

@php
    $yawAxis = data_get($turret, 'yaw_axis', []);
    $pitchAxis = data_get($turret, 'pitch_axis', []);

    $primaryMetrics = array_values(array_filter([
        ['label' => 'Rotation Style', 'value' => data_get($turret, 'rotation_style'), 'format' => 'text'],
        ['label' => 'Mounts', 'value' => data_get($turret, 'mounts'), 'format' => 'integer'],
    ], static fn (array $m): bool => $m['value'] !== null));

    $hasEquippableSize = data_get($turret, 'min_size') !== null;

    $performanceMetrics = array_values(array_filter([
        ['label' => 'Yaw Speed', 'value' => data_get($yawAxis, 'speed'), 'unit' => 'deg/s', 'precision' => 2],
        ['label' => 'Yaw Time to Full Speed', 'value' => data_get($yawAxis, 'time_to_full_speed'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Pitch Speed', 'value' => data_get($pitchAxis, 'speed'), 'unit' => 'deg/s', 'precision' => 2],
        ['label' => 'Pitch Time to Full Speed', 'value' => data_get($pitchAxis, 'time_to_full_speed'), 'unit' => 's', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));

    $advancedMetrics = array_values(array_filter([
        ['label' => 'Yaw Slaved Only', 'value' => data_get($yawAxis, 'slaved_only'), 'type' => 'bool'],
        ['label' => 'Yaw Acceleration Decay', 'value' => data_get($yawAxis, 'acceleration_decay'), 'type' => 'numeric', 'precision' => 2],
        ['label' => 'Yaw Angle Limit', 'min' => data_get($yawAxis, 'angle_limit_min'), 'max' => data_get($yawAxis, 'angle_limit_max'), 'type' => 'range', 'unit' => 'deg', 'precision' => 2],
        ['label' => 'Pitch Slaved Only', 'value' => data_get($pitchAxis, 'slaved_only'), 'type' => 'bool'],
        ['label' => 'Pitch Acceleration Decay', 'value' => data_get($pitchAxis, 'acceleration_decay'), 'type' => 'numeric', 'precision' => 2],
        ['label' => 'Pitch Angle Limit', 'min' => data_get($pitchAxis, 'angle_limit_min'), 'max' => data_get($pitchAxis, 'angle_limit_max'), 'type' => 'range', 'unit' => 'deg', 'precision' => 2],
    ], static function (array $m): bool {
        if ($m['type'] === 'range') {
            return $m['min'] !== null || $m['max'] !== null;
        }

        return $m['value'] !== null;
    }));
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Turret</h2>

        <x-dl-container>
            <x-slot:head>
                @foreach ($primaryMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        @if ($metric['format'] === 'integer')
                            {{ Format::numberOrDash($metric['value']) }}
                        @else
                            {{ $metric['value'] }}
                        @endif
                    </x-dt-dd>
                @endforeach
                @if ($hasEquippableSize)
                    <x-dt-dd label="Equippable Size">{{ Format::range(data_get($turret, 'min_size'), data_get($turret, 'max_size'), '') }}</x-dt-dd>
                @endif
            </x-slot:head>

            @if ($performanceMetrics !== [])
                <x-dl-details title="Performance" :open="true">
                    <x-dl-section>
                        @foreach ($performanceMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
                                {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                </x-dl-details>
            @endif

            @if ($advancedMetrics !== [])
                <x-dl-details title="Advanced">
                    <x-dl-section>
                        @foreach ($advancedMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
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
            @endif
        </x-dl-container>
    </div>
</div>
