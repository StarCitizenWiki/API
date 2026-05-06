@use('App\Support\Format')
@php use Illuminate\Support\Str; @endphp
@props(['shield' => null])

@php
    $primaryMetrics = [
        ['label' => 'Max Health', 'value' => data_get($shield, 'max_health'), 'unit' => 'HP', 'precision' => 0],
        ['label' => 'Regen Rate', 'value' => data_get($shield, 'regen_rate'), 'unit' => 'HP/s', 'precision' => 0],
        ['label' => 'Regen Time', 'value' => data_get($shield, 'regen_time'), 'unit' => 's', 'precision' => 2],
    ];

    $reservePool = data_get($shield, 'reserve_pool', []);
    $reservePoolMetrics = [
        ['label' => 'Regen Rate', 'value' => data_get($reservePool, 'regen_rate'), 'unit' => 'HP/s', 'precision' => 0],
        ['label' => 'Regen Time', 'value' => data_get($reservePool, 'regen_time'), 'unit' => 's', 'precision' => 1],
        ['label' => 'Drain Rate Ratio', 'value' => data_get($reservePool, 'drain_rate_ratio'), 'unit' => '', 'precision' => 1],
    ];

    $regenDelay = data_get($shield, 'regen_delay', []);
    $regenDelayMetrics = [
        ['label' => 'Downed', 'value' => data_get($regenDelay, 'downed'), 'unit' => 's', 'precision' => 2],
        ['label' => 'Damage', 'value' => data_get($regenDelay, 'damage'), 'unit' => 's', 'precision' => 2],
    ];

    $absorptionRaw = data_get($shield, 'absorption', []);
    $absorptionMetrics = is_array($absorptionRaw) && $absorptionRaw !== []
        ? collect($absorptionRaw)
            ->filter(fn ($values) => is_array($values) && data_get($values, 'max') !== null && data_get($values, 'max') != 1)
            ->map(fn ($values, $type) => [
                'label' => Str::headline($type),
                'value' => data_get($values, 'max'),
            ])
            ->values()
            ->all()
        : [];

    $resistanceRaw = data_get($shield, 'resistance', []);
    $resistanceMetrics = is_array($resistanceRaw) && $resistanceRaw !== []
        ? collect($resistanceRaw)
            ->filter(fn ($values) => is_array($values) && data_get($values, 'max') != 0)
            ->map(fn ($values, $type) => [
                'label' => Str::headline($type),
                'value' => data_get($values, 'max'),
            ])
            ->values()
            ->all()
        : [];

@endphp

<x-item-card title="Shield">
    <x-dl-container>
        <x-slot:head>
            @foreach ($primaryMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-slot:head>

        <x-dl-section title="Reserve Pool">
            @foreach ($reservePoolMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Regen Delay">
            @foreach ($regenDelayMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Absorption">
            @foreach ($absorptionMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'] * 100, '%', 1) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Resistance">
            @foreach ($resistanceMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null" :dd-class="Format::colorClass($metric['value'], true)">
                    {{ Format::valueWithUnit($metric['value'] * 100, '%', 1) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
