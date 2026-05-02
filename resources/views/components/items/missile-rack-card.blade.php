@use('App\Support\Format')
@props([
    'missileRack',
])

@php
    $primaryMetrics = array_values(array_filter([
        ['label' => 'Missile Count', 'value' => data_get($missileRack, 'missile_count'), 'precision' => 0, 'prefix' => ''],
        ['label' => 'Missile Size', 'value' => data_get($missileRack, 'missile_size'), 'precision' => 0, 'prefix' => 'S'],
    ], static fn (array $m): bool => $m['value'] !== null));
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Missile Rack</h2>

        <x-dl-container>
            <x-slot:head>
                @foreach ($primaryMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ $metric['prefix'] }}{{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-slot:head>
        </x-dl-container>
    </div>
</div>
