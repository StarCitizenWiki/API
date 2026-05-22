@use('App\Support\Format')
@props([
    'miningLaser',
])

@php
    $laserPowerMin = data_get($miningLaser, 'laser_power.minimum');
    $laserPowerMax = data_get($miningLaser, 'laser_power.maximum');

    $headMetrics = [
        ['label' => 'Module Slots', 'value' => data_get($miningLaser, 'module_slots'), 'unit' => '', 'precision' => 0],
        ['label' => 'Extraction Throughput', 'value' => data_get($miningLaser, 'extraction_throughput'), 'unit' => '', 'precision' => 2],
    ];

    $rangeThrottleMetrics = [
        ['label' => 'Optimal Range', 'value' => data_get($miningLaser, 'optimal_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Maximum Range', 'value' => data_get($miningLaser, 'maximum_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Throttle Lerp Speed', 'value' => data_get($miningLaser, 'throttle_lerp_speed'), 'unit' => '', 'precision' => 2],
        ['label' => 'Throttle Minimum', 'value' => data_get($miningLaser, 'throttle_minimum'), 'unit' => '', 'precision' => 2],
    ];

    $modifierMetrics = collect(data_get($miningLaser, 'modifier_map', []))
        ->map(fn (float|int|null $value, string $key): array => [
            'label' => str_replace('_', ' ', $key),
            'value' => $value,
            'unit' => '%',
            'precision' => 1,
        ])
        ->values()
        ->all();

    $infoRows = array_values(array_filter([
        ['label' => 'Laser Power', 'value' => Format::range($laserPowerMin, $laserPowerMax, '')],
        ...collect($headMetrics)->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'], $m['unit'], $m['precision'])])->all(),
    ], static fn (array $row): bool => $row['value'] !== null && $row['value'] !== '-'));

    $rangeRows = collect($rangeThrottleMetrics)
        ->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'], $m['unit'], $m['precision'])])
        ->filter(fn (array $row): bool => $row['value'] !== null && $row['value'] !== '-')
        ->values()
        ->all();

    $modRows = collect($modifierMetrics)
        ->map(fn ($m) => ['label' => $m['label'], 'value' => Format::valueWithUnit($m['value'], $m['unit'], $m['precision']), 'class' => Format::colorClass($m['value'])])
        ->filter(fn (array $row): bool => $row['value'] !== null && $row['value'] !== '-')
        ->values()
        ->all();

    $sections = array_values(array_filter([
        $infoRows !== [] ? ['title' => 'Info', 'rows' => $infoRows] : null,
        $rangeRows !== [] ? ['title' => 'Range & Throttle', 'rows' => $rangeRows] : null,
        $modRows !== [] ? ['title' => 'Modifiers', 'rows' => $modRows] : null,
    ], static fn (?array $s): bool => $s !== null));
@endphp

<x-data-card title="Mining Laser" :sections="$sections" {{ $attributes }} />
