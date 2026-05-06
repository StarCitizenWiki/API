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

@endphp

<x-item-card title="Counter Measure">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Type" :value="$type">{{ $type }}</x-dt-dd>
        </x-slot:head>

        <x-dl-details title="Signature" :open="true">
            <x-dt-dd label="Infrared" :value="$sigInfrared">{{ Format::valueWithUnit($sigInfrared, '', 2, true) }}</x-dt-dd>
            <x-dt-dd label="Cross Section" :value="$sigCrossSection">{{ Format::valueWithUnit($sigCrossSection, '', 2, true) }}</x-dt-dd>
            <x-dt-dd label="Electromagnetic" :value="$sigElectromagnetic">{{ Format::valueWithUnit($sigElectromagnetic, '', 2, true) }}</x-dt-dd>
        </x-dl-details>
    </x-dl-container>
</x-item-card>
