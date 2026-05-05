@use('App\Support\Format')
@props([
    'counterMeasure',
])

@php
    $type = data_get($counterMeasure, 'type');

    $signature = data_get($counterMeasure, 'signature', []);
    $sigInfrared = data_get($signature, 'infrared');
    $sigCrossSection = data_get($signature, 'cross_section');
    $sigElectromagnetic = data_get($signature, 'electromagnetic');
    $sigDecibel = data_get($signature, 'decibel');

    $hasSignatureData = $sigInfrared !== null || $sigCrossSection !== null || $sigElectromagnetic !== null;
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Counter Measure</h2>

        <x-dl-section dlClass="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($type !== null)
                <x-dt-dd label="Type">{{ $type }}</x-dt-dd>
            @endif
        </x-dl-section>

        @if ($hasSignatureData)
            <x-dl-details title="Signature" :open="true">
                @if ($sigInfrared !== null)
                    <x-dt-dd label="Infrared">{{ Format::valueWithUnit($sigInfrared, '', 2, true) }}</x-dt-dd>
                @endif
                @if ($sigCrossSection !== null)
                    <x-dt-dd label="Cross Section">{{ Format::valueWithUnit($sigCrossSection, '', 2, true) }}</x-dt-dd>
                @endif
                @if ($sigElectromagnetic !== null)
                    <x-dt-dd label="Electromagnetic">{{ Format::valueWithUnit($sigElectromagnetic, '', 2, true) }}</x-dt-dd>
                @endif
            </x-dl-details>
        @endif
    </div>
</div>
