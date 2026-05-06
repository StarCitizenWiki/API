@use('App\Support\Format')
@props([
    'missileRack',
])

@php
    $primaryMetrics = [
        ['label' => 'Missile Count', 'value' => data_get($missileRack, 'missile_count'), 'precision' => 0, 'prefix' => ''],
        ['label' => 'Missile Size', 'value' => data_get($missileRack, 'missile_size'), 'precision' => 0, 'prefix' => 'S'],
    ];
@endphp

<x-item-card title="Missile Rack">
    <x-dl-container>
        <x-slot:head>
            @foreach ($primaryMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ $metric['prefix'] }}{{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-slot:head>
    </x-dl-container>
</x-item-card>
