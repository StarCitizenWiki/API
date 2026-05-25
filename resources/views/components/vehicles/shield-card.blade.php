@use('App\Support\Format')
@props(['vehicle'])

@php
    $shield = data_get($vehicle, 'shield', []);
    $hp = data_get($shield, 'hp');
    $regeneration = data_get($shield, 'regeneration');
    $regenerationTime = data_get($shield, 'regeneration_time');
    $faceType = data_get($shield, 'face_type');
    $resistance = data_get($shield, 'resistance', []);

    $sections = [];

    $infoRows = array_values(array_filter([
        ['label' => 'Face Type', 'value' => $faceType],
        ['label' => 'Hit Points', 'value' => $hp !== null ? Format::numberOrDash($hp) . ' HP' : null],
        ['label' => 'Regeneration', 'value' => $regenerationTime !== null ? trim(Format::valueWithUnit($regenerationTime, 's', 2)) : ''],
        ['label' => '', 'value' => $regeneration !== null ? Format::numberOrDash($regeneration) . ' HP/s' : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($infoRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $infoRows];
    }

    $resistanceRows = array_values(array_filter([
        ['label' => 'Physical', 'value' => data_get($resistance, 'physical.maximum')],
        ['label' => 'Energy', 'value' => data_get($resistance, 'energy.maximum')],
        ['label' => 'Distortion', 'value' => data_get($resistance, 'distortion.maximum')],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($resistanceRows !== []) {
        $sections[] = ['title' => 'Resistance', 'rows' => array_map(static fn (array $row): array => [
            'label' => $row['label'],
            'value' => Format::percentOrDash($row['value']),
            'class' => Format::colorClass($row['value'], true),
        ], $resistanceRows)];
    }

    $hasData = $sections !== [];
@endphp

@if ($hasData)
    <x-data-card title="Shield" :sections="$sections" {{ $attributes }} />
@endif
