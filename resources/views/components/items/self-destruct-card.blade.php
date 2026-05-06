@use('App\Support\Format')
@props([
    'selfDestruct',
])

@php
    $primaryMetrics = [
        ['label' => 'Damage', 'value' => data_get($selfDestruct, 'damage'), 'precision' => 0],
        ['label' => 'Countdown', 'value' => data_get($selfDestruct, 'countdown'), 'unit' => 's', 'precision' => 0],
    ];

    $radius = data_get($selfDestruct, 'radius');
    $minRadius = data_get($selfDestruct, 'min_radius');
    $physRadius = data_get($selfDestruct, 'phys_radius');
    $minPhysRadius = data_get($selfDestruct, 'min_phys_radius');
@endphp

<x-item-card title="Self Destruct">
    <x-dl-container>
        <x-slot:head>
            @foreach ($primaryMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if (isset($metric['unit']))
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    @else
                        {{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                    @endif
                </x-dt-dd>
            @endforeach
            <x-dt-dd label="Radius" :value="$minRadius ?? $radius">{{ Format::range($minRadius, $radius, 'm', 0) }}</x-dt-dd>
            <x-dt-dd label="Physical Impact Radius" :value="$minPhysRadius ?? $physRadius">{{ Format::range($minPhysRadius, $physRadius, 'm', 0) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
