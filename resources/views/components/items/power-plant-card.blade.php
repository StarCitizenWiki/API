@use('App\Support\Format')
@props([
    'powerPlant',
])

@php
    $powerOutput = data_get($powerPlant, 'power_output');
    $powerSegmentGeneration = data_get($powerPlant, 'power_segment_generation');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Power Plant</h2>

        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <x-dt-dd label="Power Segment Generation">{{ Format::valueWithUnit($powerSegmentGeneration, 'Segments', 0) }}</x-dt-dd>
        </x-dl-section>
    </div>
</div>
