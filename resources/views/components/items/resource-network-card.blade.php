@use('App\Support\Format')
@props([
    'resourceNetwork',
    'itemType',
])

@php
    $usage = data_get($resourceNetwork, 'usage');
    $generation = data_get($resourceNetwork, 'generation');

    $primaryRows = array_values(array_filter([
        $itemType !== 'PowerPlant' ? ['label' => 'Power Usage', 'value' => Format::range(data_get($usage, 'power.minimum'), data_get($usage, 'power.maximum'), 'Segments')] : null,
        $itemType !== 'Cooler' ? ['label' => 'Coolant Usage', 'value' => Format::range(data_get($usage, 'coolant.minimum'), data_get($usage, 'coolant.maximum'), 'Segments')] : null,
        $itemType === 'PowerPlant' && data_get($generation, 'power') !== null ? ['label' => 'Power Generation', 'value' => Format::valueWithUnit(data_get($generation, 'power'), 'Segments', 0)] : null,
        $itemType === 'Cooler' && data_get($generation, 'coolant') !== null ? ['label' => 'Coolant Generation', 'value' => Format::valueWithUnit(data_get($generation, 'coolant'), 'Segments', 0)] : null,
    ], static fn (?array $m): bool => $m !== null));

    $repair = data_get($resourceNetwork, 'repair');

    $repairRows = array_values(array_filter([
        data_get($repair, 'max_repair_count') !== null ? ['label' => 'Repair Count', 'value' => Format::valueWithUnit(data_get($repair, 'max_repair_count'), 'x', 0)] : null,
        data_get($repair, 'time_to_repair') !== null ? ['label' => 'Repair Time', 'value' => Format::valueWithUnit(data_get($repair, 'time_to_repair'), 's', 0)] : null,
        data_get($repair, 'health_ratio') !== null ? ['label' => 'Health Ratio', 'value' => Format::valueWithUnit(data_get($repair, 'health_ratio') * 100, '%', 1)] : null,
    ], static fn (?array $m): bool => $m !== null));

    $states = data_get($resourceNetwork, 'states', []);

    $sections = [];

    if ($primaryRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $primaryRows];
    }

    if ($repairRows !== []) {
        $sections[] = ['title' => 'Self-Repair', 'rows' => $repairRows];
    }

    foreach ($states as $state) {
        $stateDeltas = data_get($state, 'deltas', []);
        $statePowerRanges = data_get($state, 'power_ranges', []);
        $statePrefix = data_get($state, 'name', 'State');

        // Pad so each state group starts on a fresh row of 3
        while (count($sections) % 3 !== 0) {
            $sections[] = ['spacer' => true];
        }

        foreach ($stateDeltas as $delta) {
            $deltaTitle = trim(($delta['resource'] ?? '') . ' ' . ($delta['type'] ?? ''));
            $deltaRows = array_values(array_filter([
                data_get($delta, 'rate') !== null
                    ? ['label' => 'Rate', 'value' => Format::number($delta['rate'], 1)] : null,
                data_get($delta, 'minimum_fraction') !== null
                    ? ['label' => 'Min. Fraction', 'value' => Format::valueWithUnit($delta['minimum_fraction'] * 100, '%', 1)] : null,
                data_get($delta, 'generated_resource')
                    ? ['label' => 'Generated Resource', 'value' => $delta['generated_resource']] : null,
                data_get($delta, 'generated_rate') !== null
                    ? ['label' => 'Generated Rate', 'value' => Format::number($delta['generated_rate'], 1)] : null,
                data_get($delta, 'discharge') !== null
                    ? ['label' => 'Discharge', 'value' => $delta['discharge'] ? 'Yes' : 'No'] : null,
            ], static fn (?array $m): bool => $m !== null));

            if ($deltaRows !== []) {
                $sections[] = ['title' => $statePrefix . ' - ' . $deltaTitle, 'rows' => $deltaRows];
            }
        }

        // Pad so power ranges always start on a fresh row of 3
        while (count($sections) % 3 !== 0) {
            $sections[] = ['spacer' => true];
        }

        if (is_array($statePowerRanges) && count($statePowerRanges) > 0) {

            foreach ($statePowerRanges as $i => $range) {
                $rangeTitle = match ($i) { 0 => 'Low', 1 => 'Standard', 2 => 'High', default => '' };
                if (data_get($range, 'register_range') === 0) {
                    $rangeTitle .= ' (Disabled)';
                }

                $rangeRows = array_values(array_filter([
                    data_get($range, 'start') !== null
                        ? ['label' => 'Start', 'value' => Format::valueWithUnit(data_get($range, 'start'), '', 0)] : null,
                    data_get($range, 'modifier') !== null
                        ? ['label' => 'Modifier', 'value' => Format::valueWithUnit(data_get($range, 'modifier'), 'x', 2)] : null,
                ], static fn (?array $m): bool => $m !== null));

                if ($rangeRows !== []) {
                    $sections[] = ['title' => $statePrefix . ' - ' . $rangeTitle, 'rows' => $rangeRows];
                }
            }
        }
    }
@endphp

<x-data-card title="Resource Network" :sections="$sections" {{ $attributes }} />
