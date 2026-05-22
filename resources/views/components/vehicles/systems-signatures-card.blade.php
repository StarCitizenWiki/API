@use('App\Support\Format')
@props(['vehicle'])

@php
    $signature = data_get($vehicle, 'signature', []);
    $cooling = data_get($vehicle, 'cooling', []);
    $power = data_get($vehicle, 'power', []);

    $filterNotEmpty = static fn (array $row): bool => $row['values'][0] !== '-' || $row['values'][1] !== '-';

    $sections = [];

    $sigRows = array_values(array_filter([
        ['label' => 'IR', 'values' => [
            Format::numberOrDash(data_get($signature, 'ir_shields')),
            Format::numberOrDash(data_get($signature, 'ir_quantum')),
        ]],
        ['label' => 'EM', 'values' => [
            Format::numberOrDash(data_get($signature, 'em_shields')),
            Format::numberOrDash(data_get($signature, 'em_quantum')),
        ]],
    ], $filterNotEmpty));

    if ($sigRows !== []) {
        $sections[] = ['title' => 'Signature', 'colHeader' => 'Signal', 'columns' => ['Shields', 'Quantum'], 'rows' => $sigRows];
    }

    $coolingGeneration = data_get($cooling, 'generation_segments');
    $coolingRows = array_values(array_filter([
        ['label' => 'Used', 'values' => [
            data_get($cooling, 'used_segments_shields') !== null ? Format::valueWithUnit(data_get($cooling, 'used_segments_shields'), 'Seg.', 0) : '-',
            data_get($cooling, 'used_segments_quantum') !== null ? Format::valueWithUnit(data_get($cooling, 'used_segments_quantum'), 'Seg.', 0) : '-',
        ]],
        ['label' => 'Usage', 'values' => [
            data_get($cooling, 'usage_shields_pct') !== null ? Format::valueWithUnit(data_get($cooling, 'usage_shields_pct') * 100, '%', 1) : '-',
            data_get($cooling, 'usage_quantum_pct') !== null ? Format::valueWithUnit(data_get($cooling, 'usage_quantum_pct') * 100, '%', 1) : '-',
        ]],
    ], $filterNotEmpty));

    if ($coolingGeneration !== null || $coolingRows !== []) {
        $header = $coolingGeneration !== null ? Format::valueWithUnit($coolingGeneration, 'Segments', 0) : null;
        $sections[] = ['title' => 'Cooling', 'header' => $header, 'colHeader' => 'Seg.', 'columns' => ['Shields', 'Quantum'], 'rows' => $coolingRows];
    }

    $powerGeneration = data_get($power, 'generation_segments');
    $emPerSegment = data_get($signature, 'em_per_segment');
    $powerRows = array_values(array_filter([
        ['label' => 'Used', 'values' => [
            data_get($power, 'used_segments_shields') !== null ? Format::valueWithUnit(data_get($power, 'used_segments_shields'), 'Seg.', 0) : '-',
            data_get($power, 'used_segments_quantum') !== null ? Format::valueWithUnit(data_get($power, 'used_segments_quantum'), 'Seg.', 0) : '-',
        ]],
    ], $filterNotEmpty));

    if ($powerGeneration !== null || $emPerSegment !== null || $powerRows !== []) {
        $headerParts = [];
        if ($powerGeneration !== null) {
            $headerParts[] = Format::valueWithUnit($powerGeneration, 'Segments', 0);
        }
        if ($emPerSegment !== null) {
            $headerParts[] = Format::valueWithUnit($emPerSegment, 'EM/Seg.', 0);
        }
        $sections[] = ['title' => 'Power', 'header' => $headerParts !== [] ? implode(' / ', $headerParts) : null, 'colHeader' => 'Seg.', 'columns' => ['Shields', 'Quantum'], 'rows' => $powerRows];
    }
@endphp

<x-data-card title="Resource Network" :sections="$sections" {{ $attributes }} />
