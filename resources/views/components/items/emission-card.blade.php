@use('App\Support\Format')
@props([
    'emission',
])

@php
    $ir = data_get($emission, 'ir');
    $emMin = data_get($emission, 'em_min');
    $emMax = data_get($emission, 'em_max');
    $emDecay = data_get($emission, 'em_decay');
    $emPerSegment = data_get($emission, 'em_per_segment');

    $sections = array_values(array_filter([
        [
            'title' => 'IR',
            'rows' => array_values(array_filter([
                ['label' => 'Emission', 'value' => Format::numberOrDash($ir, 1)],
            ], static fn (array $row): bool => $row['value'] !== '-')),
        ],
        [
            'title' => 'EM',
            'rows' => array_values(array_filter([
                ['label' => 'Emission', 'value' => Format::range($emMin, $emMax, '', 1)],
                ['label' => 'Decay', 'value' => Format::numberOrDash($emDecay, 2)],
                $emPerSegment !== null ? ['label' => 'Per Segment', 'value' => Format::numberOrDash($emPerSegment, 0)] : null,
            ], static fn (?array $row): bool => $row !== null && $row['value'] !== '-')),
        ],
    ], static fn (array $section): bool => ($section['rows'] ?? []) !== []));
@endphp

<x-data-card title="Emission" :sections="$sections" />
