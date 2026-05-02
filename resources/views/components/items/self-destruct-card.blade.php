@use('App\Support\Format')
@props([
    'selfDestruct',
])

@php
    $primaryMetrics = array_values(array_filter([
        ['label' => 'Damage', 'value' => data_get($selfDestruct, 'damage'), 'precision' => 0],
        ['label' => 'Countdown', 'value' => data_get($selfDestruct, 'countdown'), 'unit' => 's', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));

    $radius = data_get($selfDestruct, 'radius');
    $minRadius = data_get($selfDestruct, 'min_radius');
    $physRadius = data_get($selfDestruct, 'phys_radius');
    $minPhysRadius = data_get($selfDestruct, 'min_phys_radius');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Self Destruct</h2>

        <x-dl-container>
            <x-slot:head>
                @foreach ($primaryMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        @if (isset($metric['unit']))
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                        @endif
                    </x-dt-dd>
                @endforeach
                <x-dt-dd label="Radius">{{ Format::range($minRadius, $radius, 'm', 0) }}</x-dt-dd>
                <x-dt-dd label="Physical Impact Radius">{{ Format::range($minPhysRadius, $physRadius, 'm', 0) }}</x-dt-dd>
            </x-slot:head>
        </x-dl-container>
    </div>
</div>
