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
            'unit' => 's',
            'precision' => 1,
        ],
        [
            'label' => 'Regen Delay',
            'value' => data_get($afterburner, 'regen_delay'),
            'unit' => 's',
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
        ],
        [
            'label' => 'Yaw',
            'value' => data_get($agility, 'yaw'),
        ],
        [
            'label' => 'Roll',
            'value' => data_get($agility, 'roll'),
        ],
        [
            'label' => 'Pitch Boost',
            'value' => data_get($agility, 'pitch_boosted'),
        ],
        [
            'label' => 'Yaw Boost',
            'value' => data_get($agility, 'yaw_boosted'),
        ],
        [
            'label' => 'Roll Boost',
            'value' => data_get($agility, 'roll_boosted'),
        ],
    ];

    $agilityMetrics = array_values(array_filter(
        $agilityMetrics,
        static fn (array $metric): bool => $metric['value'] !== null
    ));
@endphp

@if ($boostMetrics !== [] || $agilityMetrics !== [])
    <div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Flight Characteristics</h2>

            @if ($boostMetrics !== [])
                <div class="space-y-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Boost</h3>
                    <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($boostMetrics as $metric)
                            <div class="space-y-1">
                                <dt class="text-xs text-base-content/60">{{ $metric['label'] }}</dt>
                                <dd class="text-sm font-medium text-base-content">
                                    {{ fmt_value_with_unit($metric['value'], $metric['unit'], $metric['precision']) }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            @if ($agilityMetrics !== [])
                <div class="space-y-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Agility</h3>
                    <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($agilityMetrics as $metric)
                            <div class="space-y-1">
                                <dt class="text-xs text-base-content/60">{{ $metric['label'] }}</dt>
                                <dd class="text-sm font-medium text-base-content">
                                    {{ number_format((float) $metric['value'], 1) }} °/s
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </div>
@endif
