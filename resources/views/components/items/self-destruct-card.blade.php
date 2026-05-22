@use('App\Support\Format')
@props([
    'selfDestruct',
])

@php
    $radius = data_get($selfDestruct, 'radius');
    $minRadius = data_get($selfDestruct, 'min_radius');
    $physRadius = data_get($selfDestruct, 'phys_radius');
    $minPhysRadius = data_get($selfDestruct, 'min_phys_radius');

    $sections = [
        [
            'title' => 'Info',
            'rows' => array_values(array_filter([
                ['label' => 'Damage', 'value' => Format::numberOrDash(data_get($selfDestruct, 'damage'), 0)],
                ['label' => 'Countdown', 'value' => Format::valueWithUnit(data_get($selfDestruct, 'countdown'), 's', 0)],
                ['label' => 'Radius', 'value' => Format::range($minRadius, $radius, 'm', 0)],
                ['label' => 'Physical Impact Radius', 'value' => Format::range($minPhysRadius, $physRadius, 'm', 0)],
            ], static fn (array $row): bool => $row['value'] !== '-' && $row['value'] !== null)),
        ],
    ];
@endphp

<x-data-card title="Self Destruct" :sections="$sections" />
