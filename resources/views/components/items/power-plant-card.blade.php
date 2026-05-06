@use('App\Support\Format')
@props([
    'powerPlant',
])

@php
    $powerOutput = data_get($powerPlant, 'power_output');
    $powerSegmentGeneration = data_get($powerPlant, 'power_segment_generation');

@endphp

<x-item-card title="Power Plant">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Power Segment Generation" :value="$powerSegmentGeneration">{{ Format::valueWithUnit($powerSegmentGeneration, 'Segments', 0) }}</x-dt-dd>
        </x-slot:head>
    </x-dl-container>
</x-item-card>
