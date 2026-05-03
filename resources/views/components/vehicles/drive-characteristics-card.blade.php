@use('App\Support\Format')
@props(['vehicle'])

@php
    $drive = data_get($vehicle, 'drive', []);

    $speedMetrics = array_values(array_filter([
        [
            'label' => 'Top Speed',
            'value' => data_get($drive, 'max_speed_kph'),
            'unit' => 'km/h',
            'precision' => 1,
        ],
        [
            'label' => 'Reverse',
            'value' => data_get($drive, 'reverse_speed_kph'),
            'unit' => 'km/h',
            'precision' => 1,
        ],
    ], static fn (array $metric): bool => $metric['value'] !== null));

    $isTracked = data_get($drive, 'is_tracked');
    $wheelType = $isTracked === true ? 'Tracked' : 'Wheeled';

    $wheels = data_get($drive, 'wheels', []);
    $wheelMetrics = array_values(array_filter([
        [
            'label' => 'Total',
            'value' => data_get($wheels, 'count'),
            'unit' => '',
            'precision' => 0,
        ],
        [
            'label' => 'Driven',
            'value' => data_get($wheels, 'driving_count'),
            'unit' => '',
            'precision' => 0,
        ],
        [
            'label' => 'Steered',
            'value' => data_get($wheels, 'steering_count'),
            'unit' => '',
            'precision' => 0,
        ],
        [
            'label' => 'Drive',
            'value' => data_get($wheels, 'drive_type'),
        ],
    ], static fn (array $metric): bool => $metric['value'] !== null));

    $stanceSpeed = data_get($drive, 'stance_speed', []);
    $stanceSpeedMetrics = array_values(array_filter([
        [
            'label' => 'Walk Speed',
            'value' => data_get($stanceSpeed, 'walk_kph'),
            'unit' => 'km/h',
            'precision' => 1,
        ],
        [
            'label' => 'Sprint Speed',
            'value' => data_get($stanceSpeed, 'sprint_kph'),
            'unit' => 'km/h',
            'precision' => 1,
        ],
        [
            'label' => 'Acceleration',
            'value' => data_get($stanceSpeed, 'acceleration'),
            'unit' => '',
            'precision' => 1,
        ],
        [
            'label' => 'Rotation Speed',
            'value' => data_get($stanceSpeed, 'rotation_speed'),
            'unit' => '°/s',
            'precision' => 0,
        ],
    ], static fn (array $metric): bool => $metric['value'] !== null));
@endphp

@if ($drive !== [])
    <div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Drive Characteristics</h2>


            <div class="grid gap-6 lg:grid-cols-2">
                @if ($speedMetrics !== [])
                    <x-dl-section title="Speed">
                        @foreach ($speedMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
                                {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif

                @if ($wheelMetrics !== [])
                    <x-dl-section :title="$wheelType">
                        @foreach ($wheelMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
                                @isset($metric['precision'])
                                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                                @else
                                    {{ $metric['value'] }}
                                @endisset
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif

                @if ($stanceSpeedMetrics !== [])
                    <x-dl-section title="Stance Speed">
                        @foreach ($stanceSpeedMetrics as $metric)
                            <x-dt-dd :label="$metric['label']">
                                {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif
                <span class="label text-xs col-span-2">Calculated based on the vehicles engine internals, they may not reflect actual in-game values.</span>
            </div>
        </div>
    </div>
@endif
