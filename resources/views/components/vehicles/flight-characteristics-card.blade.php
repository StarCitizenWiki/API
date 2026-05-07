@use('App\Support\Format')
@props(['vehicle'])

@php
    $speed = data_get($vehicle, 'speed', []);
    $agility = data_get($vehicle, 'agility', []);
    $afterburner = data_get($vehicle, 'afterburner', []);

    $boostMetrics = [
        [
            'label' => 'Forward',
            'value' => data_get($speed, 'boost_forward'),
            'unit' => 'm/s',
            'precision' => 0,
        ],
        [
            'label' => 'Reverse',
            'value' => data_get($speed, 'boost_backward'),
            'unit' => 'm/s',
            'precision' => 0,
        ],
        [
            'label' => 'Regen Time',
            'value' => data_get($afterburner, 'regen_time'),
            'delay' => data_get($afterburner, 'regen_delay'),
            'precision' => 1,
        ],
    ];

    $boostMetrics = array_values(array_filter(
        $boostMetrics,
        static fn (array $metric): bool => $metric['value'] !== null
    ));

    $agilityMetrics = [
        [
            'label' => 'Pitch',
            'value' => data_get($agility, 'pitch'),
            'boosted' => data_get($agility, 'pitch_boosted'),
        ],
        [
            'label' => 'Yaw',
            'value' => data_get($agility, 'yaw'),
            'boosted' => data_get($agility, 'yaw_boosted'),
        ],
        [
            'label' => 'Roll',
            'value' => data_get($agility, 'roll'),
            'boosted' => data_get($agility, 'roll_boosted'),
        ],
    ];

    $agilityMetrics = array_values(array_filter(
        $agilityMetrics,
        static fn (array $metric): bool => $metric['value'] !== null || $metric['boosted'] !== null
    ));
@endphp

@if ($boostMetrics !== [] || $agilityMetrics !== [])
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Flight Characteristics</h2>

            <div class="grid gap-6 lg:grid-cols-2">
                @if ($boostMetrics !== [])
                    <x-dl-section title="Boost">
                        @foreach ($boostMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
                                {{ Format::valueWithUnit($metric['value'], $metric['unit'] ?? 's', $metric['precision']) }}

                                @if (array_key_exists('delay', $metric) && $metric['delay'] !== null)
                                    <span class="inline-block whitespace-nowrap text-muted">
                                        (+ {{ Format::valueWithUnit($metric['delay'], 's', $metric['precision']) }})
                                    </span>
                                @endif
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif

                @if ($agilityMetrics !== [])
                    <x-dl-section title="Agility">
                        @foreach ($agilityMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
                                @if ($metric['value'] !== null || $metric['boosted'] !== null)
                                    <span class="inline-flex flex-nowrap items-baseline justify-end gap-1 whitespace-nowrap">
                                        @if ($metric['value'] !== null)
                                            <span>{{ number_format((float) $metric['value'], 1) }} °/s</span>
                                        @endif

                                        @if ($metric['boosted'] !== null)
                                            <span class="text-muted">
                                                @if ($metric['value'] !== null)
                                                    (boost {{ number_format((float) $metric['boosted'], 1) }} °/s)
                                                @else
                                                    boost {{ number_format((float) $metric['boosted'], 1) }} °/s
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                @else
                                    -
                                @endif
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif
            </div>
        </div>
    </section>
@endif
