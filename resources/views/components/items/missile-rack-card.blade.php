@use('App\Support\Format')
@props([
    'missileRack',
])

@php
    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'Missile Count', 'value' => Format::numberOrDash(data_get($missileRack, 'missile_count'), 0)],
                ['label' => 'Missile Size', 'value' => data_get($missileRack, 'missile_size') !== null ? 'S' . Format::numberOrDash(data_get($missileRack, 'missile_size'), 0) : '-'],
            ], static fn (array $row): bool => $row['value'] !== '-')),
        ],
    ];
@endphp

<x-data-card title="Missile Rack" :sections="$sections" />
