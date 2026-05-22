@use('App\Support\Format')
@props(['vehicle'])

@php
    $dimension = data_get($vehicle, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');

    $crossSection = data_get($vehicle, 'cross_section', []);
    $crossSectionLength = data_get($crossSection, 'length');
    $crossSectionWidth = data_get($crossSection, 'width');
    $crossSectionHeight = data_get($crossSection, 'height');

    $massTotal = data_get($vehicle, 'mass_total');
    $massHull = data_get($vehicle, 'mass_hull');
    $massLoadout = data_get($vehicle, 'mass_loadout');

    $sections = [];

    $dimRows = array_values(array_filter([
        $length !== null ? ['label' => 'Length', 'value' => Format::valueWithUnit($length, 'm', 1)] : null,
        $width !== null ? ['label' => 'Width', 'value' => Format::valueWithUnit($width, 'm', 1)] : null,
        $height !== null ? ['label' => 'Height', 'value' => Format::valueWithUnit($height, 'm', 1)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($dimRows !== []) {
        $sections[] = ['title' => 'Dimensions', 'rows' => $dimRows];
    }

    $csRows = array_values(array_filter([
        $crossSectionLength !== null ? ['label' => 'Length', 'value' => Format::numberOrDash($crossSectionLength)] : null,
        $crossSectionWidth !== null ? ['label' => 'Width', 'value' => Format::numberOrDash($crossSectionWidth)] : null,
        $crossSectionHeight !== null ? ['label' => 'Height', 'value' => Format::numberOrDash($crossSectionHeight)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($csRows !== []) {
        $sections[] = ['title' => 'Cross Section', 'rows' => $csRows];
    }

    $massRows = array_values(array_filter([
        $massTotal !== null ? ['label' => 'Total', 'value' => Format::valueWithUnit($massTotal, 'kg', 0)] : null,
        $massHull !== null ? ['label' => 'Hull', 'value' => Format::valueWithUnit($massHull, 'kg', 0)] : null,
        $massLoadout !== null ? ['label' => 'Loadout', 'value' => Format::valueWithUnit($massLoadout, 'kg', 0)] : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($massRows !== []) {
        $sections[] = ['title' => 'Mass', 'rows' => $massRows];
    }
@endphp

<x-data-card title="Dimensions & Mass" :sections="$sections" {{ $attributes }} />
