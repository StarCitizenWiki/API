@props(['vehicle'])

@php
    $crew = data_get($vehicle, 'crew', []);
    $crewMinimum = data_get($crew, 'min');
    $crewMaximum = data_get($crew, 'max');

    if ($crewMinimum !== null && $crewMaximum !== null && $crewMaximum !== $crewMinimum) {
        $crewValue = sprintf('%s-%s', $crewMinimum, $crewMaximum);
    } else {
        $crewValue = $crewMinimum ?? $crewMaximum ?? '-';
    }

    $massTotal = data_get($vehicle, 'mass_total', data_get($vehicle, 'mass'));

    $dimension = data_get($vehicle, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');

    if ($length || $width || $height) {
        $dimensionsValue = sprintf('%s × %s × %sm', $length ?? '-', $width ?? '-', $height ?? '-');
    } else {
        $dimensionsValue = '-';
    }

    $coreSpecs = [
        [
            'label' => 'Crew',
            'value' => $crewValue,
        ],
        [
            'label' => 'Mass',
            'value' => fmt_value_with_unit($massTotal, 'kg', 0),
        ],
        [
            'label' => 'Dimensions',
            'value' => $dimensionsValue,
        ],
        [
            'label' => 'Cross Section',
            'value' => fmt_or_dash(data_get($vehicle, 'cross_section_max')),
        ],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base">Core Specs</h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @foreach ($coreSpecs as $spec)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/55">
                        {{ $spec['label'] }}
                    </dt>
                    <dd class="text-sm font-medium text-base-content">
                        {{ $spec['value'] }}
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</div>
