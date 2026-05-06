@use('App\Support\Format;use Illuminate\Support\Str')
@props([
    'bomb',
])

@php
    $explosion = data_get($bomb, 'explosion', []);

    $timingMetrics = [
        ['label' => 'Arm Time', 'value' => data_get($bomb, 'arm_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Ignite Time', 'value' => data_get($bomb, 'ignite_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Collision Delay Time', 'value' => data_get($bomb, 'collision_delay_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Maximum Drop Angle', 'value' => data_get($bomb, 'maximum_drop_angle'), 'unit' => 'deg', 'precision' => 0],
    ];

    $explosionMetrics = [
        ['label' => 'Requires Launcher', 'value' => data_get($explosion, 'requires_launcher'), 'type' => 'bool'],
        ['label' => 'Radius', 'min' => data_get($explosion, 'radius_min'), 'max' => data_get($explosion, 'radius_max'), 'type' => 'range', 'unit' => 'm', 'precision' => 2],
        ['label' => 'Safety Distance', 'value' => data_get($explosion, 'safety_distance'), 'type' => 'numeric', 'unit' => 'm', 'precision' => 2],
        ['label' => 'Proximity', 'value' => data_get($explosion, 'proximity'), 'type' => 'numeric', 'unit' => 'm', 'precision' => 2],
    ];

    $damageMap = array_filter(data_get($bomb, 'damage_map', []), static fn ($value): bool => $value != 0);

@endphp

<x-item-card title="Bomb">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Damage Total" :value="data_get($bomb, 'damage_total')">{{ Format::numberOrDash(data_get($bomb, 'damage_total'), 0) }}</x-dt-dd>
        </x-slot:head>

        <x-dl-section title="Timing">
            @foreach ($timingMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Explosion">
            @foreach ($explosionMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['type'] === 'range' ? ($metric['min'] ?? $metric['max']) : $metric['value']">
                    @if ($metric['type'] === 'bool')
                        {{ $metric['value'] ? 'Yes' : 'No' }}
                    @elseif ($metric['type'] === 'range')
                        {{ Format::range($metric['min'], $metric['max'], $metric['unit'], $metric['precision']) }}
                    @else
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Damage Breakdown">
            @foreach ($damageMap as $type => $value)
                <x-dt-dd label="{{ Str::headline($type) }}">{{ Format::numberOrDash($value, 0) }}</x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
