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
    <div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Flight Characteristics</h2>

            <div class="grid gap-6 xl:grid-cols-2">
                @if ($boostMetrics !== [])
                    <section class="space-y-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Boost</h3>
                        <dl class="space-y-2">
                            @foreach ($boostMetrics as $metric)
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="min-w-0 text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ $metric['label'] }}</dt>
                                    <dd class="shrink-0 text-right text-sm font-medium text-base-content">
                                        {{ fmt_value_with_unit($metric['value'], $metric['unit'] ?? 's', $metric['precision']) }}

                                        @if (array_key_exists('delay', $metric) && $metric['delay'] !== null)
                                            <span class="inline-block whitespace-nowrap text-base-content/45">
                                                (+ {{ fmt_value_with_unit($metric['delay'], 's', $metric['precision']) }})
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($agilityMetrics !== [])
                    <section class="space-y-3">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Agility</h3>
                        <dl class="space-y-2">
                            @foreach ($agilityMetrics as $metric)
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="min-w-0 text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ $metric['label'] }}</dt>
                                    <dd class="shrink-0 text-right text-sm font-medium text-base-content">
                                        @if ($metric['value'] !== null || $metric['boosted'] !== null)
                                            <span class="inline-flex flex-nowrap items-baseline justify-end gap-1 whitespace-nowrap">
                                                @if ($metric['value'] !== null)
                                                    <span>{{ number_format((float) $metric['value'], 1) }} °/s</span>
                                                @endif

                                                @if ($metric['boosted'] !== null)
                                                    <span class="text-base-content/45">
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
