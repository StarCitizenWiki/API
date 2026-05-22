@use('App\Support\Format')
@props([
    'powerPlant',
])

@php
    $powerSegmentGeneration = data_get($powerPlant, 'power_segment_generation');

    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'Power Segment Generation', 'value' => Format::valueWithUnit($powerSegmentGeneration, 'Segments', 0)],
            ], static fn (array $row): bool => $row['value'] !== '-')),
        ],
    ];
@endphp

<x-data-card title="Power Plant" :sections="$sections" />
