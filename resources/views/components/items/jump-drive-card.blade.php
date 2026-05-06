@use('App\Support\Format')
@props([
    'jumpDrive',
])

@php
    $primaryMetrics = [
        ['label' => 'Fuel Usage Efficiency', 'value' => data_get($jumpDrive, 'fuel_usage_efficiency_multiplier'), 'unit' => 'x', 'precision' => 2],
        ['label' => 'Alignment Rate', 'value' => data_get($jumpDrive, 'alignment_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Alignment Decay Rate', 'value' => data_get($jumpDrive, 'alignment_decay_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Tuning Rate', 'value' => data_get($jumpDrive, 'tuning_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Tuning Decay Rate', 'value' => data_get($jumpDrive, 'tuning_decay_rate'), 'unit' => '', 'precision' => 2],
    ];
@endphp

<x-item-card title="Jump Drive">
    <x-dl-container>
        <x-slot:head>
            @foreach ($primaryMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-slot:head>
    </x-dl-container>
</x-item-card>
