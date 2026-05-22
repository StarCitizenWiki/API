@use('App\Support\Format')
@props(['vehicle'])

@php
    $speed = data_get($vehicle, 'speed', []);
    $agility = data_get($vehicle, 'agility', []);
    $afterburner = data_get($vehicle, 'afterburner', []);

    $sections = [];

    $speedRows = array_values(array_filter([
        ['label' => 'SCM', 'value' => data_get($speed, 'scm') !== null ? Format::valueWithUnit(data_get($speed, 'scm'), 'm/s', 0) : null],
        ['label' => 'NAV', 'value' => data_get($speed, 'max') !== null ? Format::valueWithUnit(data_get($speed, 'max'), 'm/s', 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($speedRows !== []) {
        $sections[] = ['title' => 'Speed', 'rows' => $speedRows];
    }

    $boostForward = data_get($speed, 'boost_forward');
    $boostBackward = data_get($speed, 'boost_backward');
    $regenTime = data_get($afterburner, 'regen_time');
    $regenDelay = data_get($afterburner, 'regen_delay');

    $boostRows = array_values(array_filter([
        ['label' => 'Forward', 'value' => $boostForward !== null ? Format::valueWithUnit($boostForward, 'm/s', 0) : null],
        ['label' => 'Reverse', 'value' => $boostBackward !== null ? Format::valueWithUnit($boostBackward, 'm/s', 0) : null],
        ['label' => 'Regen Time', 'value' => $regenTime !== null ? collect([
            Format::valueWithUnit($regenTime, 's', 1),
            $regenDelay !== null ? '(+ ' . Format::valueWithUnit($regenDelay, 's', 1) . ')' : null,
        ])->filter()->join(' ') : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($boostRows !== []) {
        $sections[] = ['title' => 'Boost', 'rows' => $boostRows];
    }

    $agilityKeys = [
        ['label' => 'Pitch', 'key' => 'pitch', 'boostKey' => 'pitch_boosted'],
        ['label' => 'Yaw', 'key' => 'yaw', 'boostKey' => 'yaw_boosted'],
        ['label' => 'Roll', 'key' => 'roll', 'boostKey' => 'roll_boosted'],
    ];

    $agilityRows = [];
    foreach ($agilityKeys as $item) {
        $val = data_get($agility, $item['key']);
        $boosted = data_get($agility, $item['boostKey']);

        if ($val === null && $boosted === null) {
            continue;
        }

        $parts = [];
        if ($val !== null) {
            $parts[] = Format::number((float) $val, 1) . ' °/s';
        }

        if ($boosted !== null) {
            $parts[] = ($val !== null ? '(boost ' : 'boost ') . Format::number((float) $boosted, 1) . ' °/s' . ($val !== null ? ')' : '');
        }

        $agilityRows[] = ['label' => $item['label'], 'value' => implode(' ', $parts)];
    }

    if ($agilityRows !== []) {
        $sections[] = ['title' => 'Agility', 'rows' => $agilityRows];
    }
@endphp

<x-data-card title="Flight Characteristics" :sections="$sections" {{ $attributes }} />
