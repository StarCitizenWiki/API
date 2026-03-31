@props(['vehicle'])

@php
    $fuel = data_get($vehicle, 'fuel', []);
    $quantum = data_get($vehicle, 'quantum', []);

    $capacityMetrics = [
        [
            'label' => 'Fuel',
            'value' => data_get($fuel, 'capacity'),
            'unit' => 'SCU',
            'precision' => 0,
        ],
        [
            'label' => 'Quantum',
            'value' => data_get($quantum, 'quantum_fuel_capacity'),
            'unit' => 'SCU',
            'precision' => 2,
        ],
    ];

    $capacityMetrics = array_values(array_filter(
        $capacityMetrics,
        static fn (array $metric): bool => $metric['value'] !== null
    ));

    $travelMetrics = [
        [
            'label' => 'Speed',
            'value' => data_get($quantum, 'quantum_speed'),
            'formatter' => static fn (mixed $value): string => fmt_compact($value, 0).' m/s',
        ],
        [
            'label' => 'Spool Time',
            'value' => data_get($quantum, 'quantum_spool_time'),
            'formatter' => static fn (mixed $value): string => fmt_value_with_unit($value, 's', 2),
        ],
        [
            'label' => 'Range',
            'value' => data_get($quantum, 'quantum_range'),
            'formatter' => static fn (mixed $value): string => fmt_compact($value, 2).' m',
        ],
    ];

    $travelMetrics = array_values(array_filter(
        $travelMetrics,
        static fn (array $metric): bool => $metric['value'] !== null
    ));
@endphp

@if ($capacityMetrics !== [] || $travelMetrics !== [])
    <div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Fuel & Quantum</h2>

            <div class="grid gap-6 lg:grid-cols-2">
                @if ($capacityMetrics !== [])
                    <section class="space-y-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</h3>
                        <dl class="space-y-2">
                            @foreach ($capacityMetrics as $metric)
                                <div class="grid grid-cols-2 items-start gap-x-3">
                                    <dt class="text-xs text-base-content/60">{{ $metric['label'] }}</dt>
                                    <dd class="text-right text-sm font-medium text-base-content">
                                        {{ fmt_value_with_unit($metric['value'], $metric['unit'], $metric['precision']) }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($travelMetrics !== [])
                    <section class="space-y-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Travel</h3>
                        <dl class="space-y-2">
                            @foreach ($travelMetrics as $metric)
                                <div class="grid grid-cols-2 items-start gap-x-3">
                                    <dt class="text-xs text-base-content/60">{{ $metric['label'] }}</dt>
                                    <dd class="text-right text-sm font-medium text-base-content">
                                        {{ $metric['formatter']($metric['value']) }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif
            </div>
        </div>
    </div>
@endif
