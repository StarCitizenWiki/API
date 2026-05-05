@use('App\Support\Format')
@props([
    'jumpDrive',
])

@php
    $primaryMetrics = array_values(array_filter([
        ['label' => 'Fuel Usage Efficiency', 'value' => data_get($jumpDrive, 'fuel_usage_efficiency_multiplier'), 'unit' => 'x', 'precision' => 2],
        ['label' => 'Alignment Rate', 'value' => data_get($jumpDrive, 'alignment_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Alignment Decay Rate', 'value' => data_get($jumpDrive, 'alignment_decay_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Tuning Rate', 'value' => data_get($jumpDrive, 'tuning_rate'), 'unit' => '', 'precision' => 2],
        ['label' => 'Tuning Decay Rate', 'value' => data_get($jumpDrive, 'tuning_decay_rate'), 'unit' => '', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null));
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Jump Drive</h2>

        <x-dl-container>
            <x-slot:head>
                @foreach ($primaryMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-slot:head>
        </x-dl-container>
    </div>
</div>
