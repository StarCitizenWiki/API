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

    $sections = [];

    if ($type !== null) {
        $sections[] = ['title' => 'Info', 'rows' => [
            ['label' => 'Type', 'value' => $type],
        ]];
    }

    $signatureRows = array_values(array_filter([
        $sigInfrared !== null
            ? ['label' => 'Infrared', 'value' => Format::valueWithUnit($sigInfrared, '', 2, true)]
            : null,
        $sigCrossSection !== null
            ? ['label' => 'Cross Section', 'value' => Format::valueWithUnit($sigCrossSection, '', 2, true)]
            : null,
        $sigElectromagnetic !== null
            ? ['label' => 'Electromagnetic', 'value' => Format::valueWithUnit($sigElectromagnetic, '', 2, true)]
            : null,
        $sigDecibel !== null
            ? ['label' => 'Decibel', 'value' => Format::valueWithUnit($sigDecibel, '', 2, true)]
            : null,
    ], static fn (?array $row): bool => $row !== null));

    if ($signatureRows !== []) {
        $sections[] = ['title' => 'Signature', 'rows' => $signatureRows];
    }
@endphp

<x-data-card title="Counter Measure" :sections="$sections" {{ $attributes }} />
