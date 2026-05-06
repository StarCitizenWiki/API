@use('App\Support\Format')
@props([
    'miningLaser',
])

@php
    $laserPowerMin = data_get($miningLaser, 'laser_power.minimum');
    $laserPowerMax = data_get($miningLaser, 'laser_power.maximum');

    $headMetrics = [
        ['label' => 'Module Slots', 'value' => data_get($miningLaser, 'module_slots'), 'unit' => '', 'precision' => 0],
        ['label' => 'Extraction Throughput', 'value' => data_get($miningLaser, 'extraction_throughput'), 'unit' => '', 'precision' => 2],
    ];

    $rangeThrottleMetrics = [
        ['label' => 'Optimal Range', 'value' => data_get($miningLaser, 'optimal_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Maximum Range', 'value' => data_get($miningLaser, 'maximum_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Throttle Lerp Speed', 'value' => data_get($miningLaser, 'throttle_lerp_speed'), 'unit' => '', 'precision' => 2],
        ['label' => 'Throttle Minimum', 'value' => data_get($miningLaser, 'throttle_minimum'), 'unit' => '', 'precision' => 2],
    ];

    $modifierMetrics = collect(data_get($miningLaser, 'modifier_map', []))
        ->map(fn (float|int|null $value, string $key): array => [
            'label' => str_replace('_', ' ', $key),
            'value' => $value,
            'unit' => '%',
            'precision' => 1,
        ])
        ->values()
        ->all();
@endphp

<x-item-card title="Mining Laser">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Laser Power" :value="$laserPowerMin ?? $laserPowerMax">{{ Format::range($laserPowerMin, $laserPowerMax, '') }}</x-dt-dd>
            @foreach ($headMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-slot:head>

        <x-dl-section title="Range & Throttle">
            @foreach ($rangeThrottleMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Modifiers">
            @foreach ($modifierMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    <span class="{{ Format::colorClass($metric['value']) }}">{{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}</span>
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
