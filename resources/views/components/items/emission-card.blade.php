@use('App\Support\Format')
@props([
    'emission',
])

@php
    $ir = data_get($emission, 'ir');
    $emMin = data_get($emission, 'em_min');
    $emMax = data_get($emission, 'em_max');
    $emDecay = data_get($emission, 'em_decay');
    $emPerSegment = data_get($emission, 'em_per_segment');

    $irMetrics = [
        ['label' => 'Emission', 'value' => Format::numberOrDash($ir, 1)],
    ];

    $emMetrics = array_values(array_filter([
        ['label' => 'Emission', 'value' => Format::range($emMin, $emMax, '', 1)],
        ['label' => 'Decay', 'value' => Format::numberOrDash($emDecay, 2)],
        $emPerSegment !== null ? ['label' => 'Per Segment', 'value' => Format::numberOrDash($emPerSegment, 0)] : null,
    ], static fn (?array $m): bool => $m !== null));

@endphp

<x-item-card title="Emission">
    <x-dl-container>
        <x-dl-section title="IR">
            @foreach ($irMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">{{ $metric['value'] }}</x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="EM">
            @foreach ($emMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">{{ $metric['value'] }}</x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
