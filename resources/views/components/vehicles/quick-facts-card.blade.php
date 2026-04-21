@props(['vehicle'])

@php
    $cargoCapacity = data_get($vehicle, 'cargo_capacity');
    $stowage = data_get($vehicle, 'vehicle_inventory');
    $health = data_get($vehicle, 'health');
    $shieldHp = data_get($vehicle, 'shield.hp');
    $scmSpeed = data_get($vehicle, 'speed.scm');
    $maxSpeed = data_get($vehicle, 'speed.max');
    $irShields = data_get($vehicle, 'signature.ir_shields');
    $emShields = data_get($vehicle, 'signature.em_shields');
    $crew = data_get($vehicle, 'crew', []);
    $crewMinimum = data_get($crew, 'min');
    $crewMaximum = data_get($crew, 'max');
    $massTotal = data_get($vehicle, 'mass_total', data_get($vehicle, 'mass'));
    $dimension = data_get($vehicle, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');
    $version = data_get($vehicle, 'version');

    if ($crewMinimum !== null && $crewMaximum !== null && $crewMaximum !== $crewMinimum) {
        $crewValue = sprintf('%s-%s', $crewMinimum, $crewMaximum);
    } else {
        $crewValue = $crewMinimum ?? $crewMaximum ?? '-';
    }

    if ($length || $width || $height) {
        $dimensionsValue = sprintf('%s × %s × %sm', $length ?? '-', $width ?? '-', $height ?? '-');
    } else {
        $dimensionsValue = '-';
    }

    $quickFacts = [
        [
            'label' => 'Storage',
            'primary_label' => 'Cargo',
            'primary_value' => $cargoCapacity !== null ? fmt_value_with_unit($cargoCapacity, 'SCU', 0) : '-',
            'secondary_label' => 'Stowage',
            'secondary_value' => $stowage !== null ? fmt_value_with_unit($stowage, 'µSCU', 0) : '-',
            'render' => $cargoCapacity !== null || $stowage !== null,
        ],
        [
            'label' => 'Speed',
            'primary_label' => 'SCM',
            'primary_value' => $scmSpeed !== null ? fmt_value_with_unit($scmSpeed, 'm/s', 0) : '-',
            'secondary_label' => 'Max',
            'secondary_value' => $maxSpeed !== null ? fmt_value_with_unit($maxSpeed, 'm/s', 0) : '-',
            'render' => $scmSpeed !== null || $maxSpeed !== null,
        ],
        [
            'label' => 'Defense',
            'primary_label' => 'HP',
            'primary_value' => $health !== null ? fmt_value_with_unit($health, 'HP', 0) : '-',
            'secondary_label' => 'Shield',
            'secondary_value' => $shieldHp !== null ? fmt_value_with_unit($shieldHp, 'HP', 0) : '-',
            'render' => $health !== null || $shieldHp !== null,
        ],
        [
            'label' => 'Signature',
            'primary_label' => 'IR',
            'primary_value' => fmt_or_dash($irShields),
            'secondary_label' => 'EM',
            'secondary_value' => fmt_or_dash($emShields),
            'render' => $irShields !== null || $emShields !== null,
        ],
    ];

    $quickFacts = array_values(array_filter($quickFacts, static fn (array $fact): bool => $fact['render']));

    $factColumns = [[], []];

    foreach ($quickFacts as $index => $fact) {
        $factColumns[$index % 2][] = $fact;
    }

    $factColumns = array_values(array_filter($factColumns, static fn (array $column): bool => $column !== []));

    $stats = [
        [
            'label' => 'Crew',
            'value' => $crewValue,
        ],
        [
            'label' => 'Dimensions',
            'value' => $dimensionsValue,
        ],
        [
            'label' => 'Cross Section',
            'value' => fmt_or_dash(data_get($vehicle, 'cross_section_max')),
        ],
        [
            'label' => 'Mass',
            'value' => fmt_value_with_unit($massTotal, 'kg', 0),
        ],
        [
            'label' => 'Version',
            'value' => $version ?? '-',
        ],
    ];
@endphp

@if ($quickFacts !== [])
    <section {{ $attributes->merge(['class' => 'card h-full border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body p-5 sm:p-6">
            <div class="grid h-full gap-6 xl:grid-cols-2 xl:gap-8 2xl:grid-cols-3">
                @foreach ($factColumns as $column)
                    <div class="space-y-6">
                        @foreach ($column as $fact)
                            <section class="min-w-0 space-y-3">
                                <div class="text-sm font-semibold text-base-content/65">
                                    {{ $fact['label'] }}
                                </div>

                                <dl class="grid grid-cols-2 items-start gap-x-3 gap-y-2">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                        {{ $fact['primary_label'] }}
                                    </dt>
                                    <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                        {{ $fact['primary_value'] }}
                                    </dd>

                                    <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                        {{ $fact['secondary_label'] }}
                                    </dt>
                                    <dd class="min-w-0 text-right text-sm font-semibold text-base-content">
                                        {{ $fact['secondary_value'] }}
                                    </dd>
                                </dl>
                            </section>
                        @endforeach
                    </div>
                @endforeach

                <section class="space-y-3 xl:col-span-2 2xl:col-span-1">
                    <div class="text-sm font-semibold text-base-content/65">Stats</div>

                    <dl class="space-y-2">
                        @foreach ($stats as $stat)
                            <div class="grid grid-cols-2 items-start gap-x-3">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">
                                    {{ $stat['label'] }}
                                </dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ $stat['value'] }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            </div>
        </div>
    </section>
@endif
