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

    $primaryRows = array_values(array_filter(
        collect($primaryMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'], $m['unit'], $m['precision'])])->all(),
        static fn (array $row): bool => $row['value'] !== '-' && $row['value'] !== null,
    ));

    $reserveRows = array_values(array_filter(
        collect($reservePoolMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'], $m['unit'], $m['precision'])])->all(),
        static fn (array $row): bool => $row['value'] !== '-' && $row['value'] !== null,
    ));

    $delayRows = array_values(array_filter(
        collect($regenDelayMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'], $m['unit'], $m['precision'])])->all(),
        static fn (array $row): bool => $row['value'] !== '-' && $row['value'] !== null,
    ));

    $absRows = array_values(array_filter(
        collect($absorptionMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'] * 100, '%', 1)])->all(),
        static fn (array $row): bool => $row['value'] !== '-',
    ));

    $resRows = array_values(array_filter(
        collect($resistanceMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'] * 100, '%', 1), 'class' => Format::colorClass($m['value'], true)])->all(),
        static fn (array $row): bool => $row['value'] !== '-',
    ));

    $sections = [];

    if ($primaryRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $primaryRows];
    }

    if ($reserveRows !== []) {
        $sections[] = ['title' => 'Reserve Pool', 'rows' => $reserveRows];
    }

    if ($delayRows !== []) {
        $sections[] = ['title' => 'Regen Delay', 'rows' => $delayRows];
    }

    if ($absRows !== []) {
        $sections[] = ['title' => 'Absorption', 'rows' => $absRows];
    }

    if ($resRows !== []) {
        $sections[] = ['title' => 'Resistance', 'rows' => $resRows];
    }
@endphp

<x-data-card title="Shield" :sections="$sections" />
