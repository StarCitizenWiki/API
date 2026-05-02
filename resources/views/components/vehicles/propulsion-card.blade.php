@use('App\Support\Format')
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
            'formatter' => static fn (mixed $value): string => Format::compact($value, 0).' m/s',
        ],
        [
            'label' => 'Spool Time',
            'value' => data_get($quantum, 'quantum_spool_time'),
            'formatter' => static fn (mixed $value): string => Format::valueWithUnit($value, 's', 2),
        ],
        [
            'label' => 'Range',
            'value' => data_get($quantum, 'quantum_range'),
            'formatter' => static fn (mixed $value): string => Format::compact($value, 2).' m',
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
                    <x-dl-section title="Capacity">
                        @foreach ($capacityMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
                                {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif

                @if ($travelMetrics !== [])
                    <x-dl-section title="Travel">
                        @foreach ($travelMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
                                {{ $metric['formatter']($metric['value']) }}
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif
            </div>
        </div>
    </div>
@endif
