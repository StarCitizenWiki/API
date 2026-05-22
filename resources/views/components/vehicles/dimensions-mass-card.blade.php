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

    $sections = [
        [
            'label' => 'Dimensions',
            'rows' => [
                ['label' => 'Length', 'value' => $length !== null ? Format::valueWithUnit($length, 'm', 1) : '-'],
                ['label' => 'Width', 'value' => $width !== null ? Format::valueWithUnit($width, 'm', 1) : '-'],
                ['label' => 'Height', 'value' => $height !== null ? Format::valueWithUnit($height, 'm', 1) : '-'],
            ],
            'render' => $length !== null || $width !== null || $height !== null,
        ],
        [
            'label' => 'Cross Section',
            'rows' => [
                ['label' => 'Length', 'value' => Format::numberOrDash($crossSectionLength)],
                ['label' => 'Width', 'value' => Format::numberOrDash($crossSectionWidth)],
                ['label' => 'Height', 'value' => Format::numberOrDash($crossSectionHeight)],
            ],
            'render' => $crossSectionLength !== null || $crossSectionWidth !== null || $crossSectionHeight !== null,
        ],
        [
            'label' => 'Mass',
            'rows' => [
                ['label' => 'Total', 'value' => $massTotal !== null ? Format::valueWithUnit($massTotal, 'kg', 0) : '-'],
                ['label' => 'Hull', 'value' => $massHull !== null ? Format::valueWithUnit($massHull, 'kg', 0) : '-'],
                ['label' => 'Loadout', 'value' => $massLoadout !== null ? Format::valueWithUnit($massLoadout, 'kg', 0) : '-'],
            ],
            'render' => $massTotal !== null || $massHull !== null || $massLoadout !== null,
        ],
    ];

    $sections = array_values(array_filter($sections, static fn (array $section): bool => $section['render']));
@endphp

@if ($sections !== [])
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">Dimensions & Mass</h2>

            <div class="grid gap-6 grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
                @foreach ($sections as $section)
                    <x-dl-section :title="$section['label']" class="min-w-0">
                        @foreach ($section['rows'] as $row)
                            <x-dt-dd :label="$row['label']">{{ $row['value'] }}</x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endforeach
            </div>
        </div>
    </section>
@endif
