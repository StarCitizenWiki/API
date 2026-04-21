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

    $massTotal = data_get($vehicle, 'mass_total', data_get($vehicle, 'mass'));
    $massHull = data_get($vehicle, 'mass_hull');
    $massLoadout = data_get($vehicle, 'mass_loadout');

    $sections = [
        [
            'label' => 'Dimensions',
            'rows' => [
                ['label' => 'Length', 'value' => $length !== null ? fmt_value_with_unit($length, 'm', 1) : '-'],
                ['label' => 'Width', 'value' => $width !== null ? fmt_value_with_unit($width, 'm', 1) : '-'],
                ['label' => 'Height', 'value' => $height !== null ? fmt_value_with_unit($height, 'm', 1) : '-'],
            ],
            'render' => $length !== null || $width !== null || $height !== null,
        ],
        [
            'label' => 'Cross Section',
            'rows' => [
                ['label' => 'Length', 'value' => fmt_or_dash($crossSectionLength)],
                ['label' => 'Width', 'value' => fmt_or_dash($crossSectionWidth)],
                ['label' => 'Height', 'value' => fmt_or_dash($crossSectionHeight)],
            ],
            'render' => $crossSectionLength !== null || $crossSectionWidth !== null || $crossSectionHeight !== null,
        ],
        [
            'label' => 'Mass',
            'rows' => [
                ['label' => 'Total', 'value' => $massTotal !== null ? fmt_value_with_unit($massTotal, 'kg', 0) : '-'],
                ['label' => 'Hull', 'value' => $massHull !== null ? fmt_value_with_unit($massHull, 'kg', 0) : '-'],
                ['label' => 'Loadout', 'value' => $massLoadout !== null ? fmt_value_with_unit($massLoadout, 'kg', 0) : '-'],
            ],
            'render' => $massTotal !== null || $massHull !== null || $massLoadout !== null,
        ],
    ];

    $sections = array_values(array_filter($sections, static fn (array $section): bool => $section['render']));
@endphp

@if ($sections !== [])
    <section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <h2 class="card-title text-base">Dimensions & Mass</h2>

            <div class="grid gap-12 lg:grid-cols-3">
                @foreach ($sections as $section)
                    <section class="min-w-0 space-y-3">
                        <div class="text-sm font-semibold text-base-content/65">
                            {{ $section['label'] }}
                        </div>

                        <dl class="space-y-2">
                            @foreach ($section['rows'] as $row)
                                <div class="grid grid-cols-2 items-start gap-x-3">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                        {{ $row['label'] }}
                                    </dt>
                                    <dd class="text-right text-sm font-semibold text-base-content">
                                        {{ $row['value'] }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endforeach
            </div>
        </div>
    </section>
@endif
