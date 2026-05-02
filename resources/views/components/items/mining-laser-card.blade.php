@use('App\Support\Format')
@props([
    'miningLaser',
])

@php
    $laserPowerMin = data_get($miningLaser, 'laser_power.minimum');
    $laserPowerMax = data_get($miningLaser, 'laser_power.maximum');

    $headMetrics = array_values(array_filter([
        ['label' => 'Module Slots', 'value' => data_get($miningLaser, 'module_slots'), 'unit' => '', 'precision' => 0],
        ['label' => 'Extraction Throughput', 'value' => data_get($miningLaser, 'extraction_throughput'), 'unit' => '', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));

    $rangeThrottleMetrics = array_values(array_filter([
        ['label' => 'Optimal Range', 'value' => data_get($miningLaser, 'optimal_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Maximum Range', 'value' => data_get($miningLaser, 'maximum_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Throttle Lerp Speed', 'value' => data_get($miningLaser, 'throttle_lerp_speed'), 'unit' => '', 'precision' => 2],
        ['label' => 'Throttle Minimum', 'value' => data_get($miningLaser, 'throttle_minimum'), 'unit' => '', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));

    $modifierMetrics = collect(data_get($miningLaser, 'modifier_map', []))
        ->map(fn (float|int|null $value, string $key): array => [
            'label' => str_replace('_', ' ', $key),
            'value' => $value,
            'unit' => '%',
            'precision' => 1,
        ])
        ->filter(fn (array $m): bool => $m['value'] !== null)
        ->values()
        ->all();
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Mining Laser</h2>

        <x-dl-container>
            <x-slot:head>
                @if ($laserPowerMin !== null || $laserPowerMax !== null)
                    <x-dt-dd label="Laser Power">{{ Format::range($laserPowerMin, $laserPowerMax, '') }}</x-dt-dd>
                @endif
                @foreach ($headMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-slot:head>

            @if (count($rangeThrottleMetrics) > 0)
                <x-dl-section title="Range & Throttle">
                    @foreach ($rangeThrottleMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif

            @if (count($modifierMetrics) >= 2)
                <x-dl-section title="Modifiers">
                    @foreach ($modifierMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            <span class="{{ Format::colorClass($metric['value']) }}">{{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}</span>
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif
        </x-dl-container>
    </div>
</div>
