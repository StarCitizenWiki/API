@use('App\Support\Format')
@props([
    'missile',
 ])

@php
    $signalType = data_get($missile, 'signal_type');
    $trackingSignalMin = data_get($missile, 'tracking_signal_min');
    $clusterSize = data_get($missile, 'cluster_size');
    $damageTotal = data_get($missile, 'damage_total');
    $damageMap = data_get($missile, 'damage_map', []);

    $flight = data_get($missile, 'flight', []);
    $targetLock = data_get($missile, 'target_lock', []);
    $explosion = data_get($missile, 'explosion', []);
    $delays = data_get($missile, 'delays', []);

    // Head metrics — primary always-visible fields (Signal Type and Lock Range rendered as static x-dt-dd)
    $headMetrics = array_values(array_filter([
        ['label' => 'Damage Total', 'value' => $damageTotal, 'unit' => '', 'precision' => 2],
        ['label' => 'Range', 'value' => data_get($flight, 'range'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Arm Time', 'value' => data_get($delays, 'arm_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Cluster Size', 'value' => $clusterSize, 'unit' => '', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));

    // Target Lock metrics (Lock Range rendered as static x-dt-dd)
    $targetLockMetrics = array_values(array_filter([
        ['label' => 'Lock Angle', 'value' => data_get($targetLock, 'angle'), 'unit' => 'deg', 'precision' => 1],
        ['label' => 'Tracking Signal Min', 'value' => $trackingSignalMin, 'unit' => '', 'precision' => 2],
        ['label' => 'Signal Resilience Min', 'value' => data_get($targetLock, 'signal_resilience_min'), 'unit' => '', 'precision' => 2],
        ['label' => 'Signal Resilience Max', 'value' => data_get($targetLock, 'signal_resilience_max'), 'unit' => '', 'precision' => 2],
        ['label' => 'Signal Amplifier', 'value' => data_get($targetLock, 'signal_amplifier'), 'unit' => '', 'precision' => 2],
        ['label' => 'Lock Increase Rate', 'value' => data_get($targetLock, 'increase_rate'), 'unit' => '/s', 'precision' => 2],
        ['label' => 'Allow Dumb Firing', 'value' => data_get($targetLock, 'allow_dumb_firing'), 'unit' => '', 'precision' => 0, 'format' => 'boolean'],
    ], static fn (array $m): bool => $m['value'] !== null));

    // Flight metrics
    $flightMetrics = array_values(array_filter([
        ['label' => 'Speed', 'value' => data_get($flight, 'speed'), 'unit' => 'm/s', 'precision' => 2],
        ['label' => 'Max Lifetime', 'value' => data_get($flight, 'max_lifetime'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Boost Speed', 'value' => data_get($flight, 'boost_speed'), 'unit' => 'm/s', 'precision' => 2],
        ['label' => 'Intercept Speed', 'value' => data_get($flight, 'intercept_speed'), 'unit' => 'm/s', 'precision' => 2],
        ['label' => 'Terminal Speed', 'value' => data_get($flight, 'terminal_speed'), 'unit' => 'm/s', 'precision' => 2],
        ['label' => 'Fuel Tank Size', 'value' => data_get($flight, 'fuel_tank_size'), 'unit' => '', 'precision' => 0],
        ['label' => 'Boost Phase Duration', 'value' => data_get($flight, 'boost_phase_duration'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Terminal Phase Engagement Time', 'value' => data_get($flight, 'terminal_phase_engagement_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Terminal Phase Engagement Angle', 'value' => data_get($flight, 'terminal_phase_engagement_angle'), 'unit' => 'deg', 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));

    // Damage map — dynamic key-value pairs
    $damageMetrics = array_values(array_filter(
        collect($damageMap)->map(fn (float|int|null $value, string $type): array => [
            'label' => Str::headline($type),
            'value' => $value,
            'unit' => '',
            'precision' => 2,
        ])->values()->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    // Explosion metrics (Radius rendered as static x-dt-dd)
    $explosionMetrics = array_values(array_filter([
        ['label' => 'Is Cluster', 'value' => data_get($explosion, 'is_cluster'), 'unit' => '', 'precision' => 0, 'format' => 'boolean'],
        ['label' => 'Cluster Size', 'value' => data_get($explosion, 'cluster_size'), 'unit' => '', 'precision' => 0],
        ['label' => 'Requires Launcher', 'value' => data_get($explosion, 'requires_launcher'), 'unit' => '', 'precision' => 0, 'format' => 'boolean'],
        ['label' => 'Safety Distance', 'value' => data_get($explosion, 'safety_distance'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Proximity', 'value' => data_get($explosion, 'proximity'), 'unit' => 'm', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));

    // Delays metrics
    $delaysMetrics = array_values(array_filter([
        ['label' => 'Arm Time', 'value' => data_get($delays, 'arm_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Ignite Time', 'value' => data_get($delays, 'ignite_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Collision Delay Time', 'value' => data_get($delays, 'collision_delay_time'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Lock Time', 'value' => data_get($delays, 'lock_time'), 'unit' => 's', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Missile</h2>

        <x-dl-container>
            <x-slot:head>
                @if ($signalType !== null)
                    <x-dt-dd label="Signal Type">{{ $signalType }}</x-dt-dd>
                @endif
                @if (data_get($targetLock, 'range_min') !== null)
                    <x-dt-dd label="Lock Range">{{ Format::range(data_get($targetLock, 'range_min'), data_get($targetLock, 'range_max'), 'm') }}</x-dt-dd>
                @endif
                @foreach ($headMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-slot:head>

            <x-dl-section title="Target Lock">
                @foreach ($targetLockMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        @if (($metric['format'] ?? '') === 'boolean')
                            {{ $metric['value'] ? 'Yes' : 'No' }}
                        @else
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
            <x-dl-section title="Damage">
                @foreach ($damageMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
            @if ($flightMetrics !== [] || $explosionMetrics !== [] || $delaysMetrics !== [] || data_get($explosion, 'radius_min') !== null)
            <x-dl-details title="Flight Performance, Explosion & Delays">
                <x-dl-section title="Flight Performance">
                    @foreach ($flightMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
                <x-dl-section title="Explosion">
                    @if (data_get($explosion, 'radius_min') !== null)
                        <x-dt-dd label="Radius">{{ Format::range(data_get($explosion, 'radius_min'), data_get($explosion, 'radius_max'), 'm', 2) }}</x-dt-dd>
                    @endif
                    @foreach ($explosionMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            @if (($metric['format'] ?? '') === 'boolean')
                                {{ $metric['value'] ? 'Yes' : 'No' }}
                            @else
                                {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                            @endif
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
                <x-dl-section title="Delays">
                    @foreach ($delaysMetrics as $metric)
                        <x-dt-dd :label="$metric['label']">
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            </x-dl-details>
            @endif
        </x-dl-container>
    </div>
</div>
