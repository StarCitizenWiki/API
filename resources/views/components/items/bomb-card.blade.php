@use('App\Support\Format')
@php use Illuminate\Support\Str; @endphp
@props([
    'bomb',
])

@php
    $explosion = data_get($bomb, 'explosion', []);

    $damageTotal = data_get($bomb, 'damage_total');

    $timingRows = array_values(array_filter([
        ['label' => 'Arm Time', 'value' => data_get($bomb, 'arm_time') !== null ? Format::valueWithUnit(data_get($bomb, 'arm_time'), 's', 1) : null],
        ['label' => 'Ignite Time', 'value' => data_get($bomb, 'ignite_time') !== null ? Format::valueWithUnit(data_get($bomb, 'ignite_time'), 's', 1) : null],
        ['label' => 'Collision Delay Time', 'value' => data_get($bomb, 'collision_delay_time') !== null ? Format::valueWithUnit(data_get($bomb, 'collision_delay_time'), 's', 1) : null],
        ['label' => 'Maximum Drop Angle', 'value' => data_get($bomb, 'maximum_drop_angle') !== null ? Format::valueWithUnit(data_get($bomb, 'maximum_drop_angle'), 'deg', 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    $explosionRows = array_values(array_filter([
        ['label' => 'Requires Launcher', 'value' => data_get($explosion, 'requires_launcher') !== null ? (data_get($explosion, 'requires_launcher') ? 'Yes' : 'No') : null],
        ['label' => 'Radius', 'value' => Format::range(data_get($explosion, 'radius_min'), data_get($explosion, 'radius_max'), 'm', 2)],
        ['label' => 'Safety Distance', 'value' => data_get($explosion, 'safety_distance') !== null ? Format::valueWithUnit(data_get($explosion, 'safety_distance'), 'm', 2) : null],
        ['label' => 'Proximity', 'value' => data_get($explosion, 'proximity') !== null ? Format::valueWithUnit(data_get($explosion, 'proximity'), 'm', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null && $row['value'] !== '-'));

    $damageMap = array_filter(data_get($bomb, 'damage_map', []), static fn ($value): bool => $value != 0);

    $damageRows = [];
    foreach ($damageMap as $type => $value) {
        $damageRows[] = ['label' => Str::headline($type), 'value' => Format::numberOrDash($value, 0)];
    }

    $sections = [];

    if ($damageTotal !== null) {
        $sections[] = ['title' => 'Info', 'rows' => [['label' => 'Damage Total', 'value' => Format::numberOrDash($damageTotal, 0)]]];
    }

    if ($timingRows !== []) {
        $sections[] = ['title' => 'Timing', 'rows' => $timingRows];
    }

    if ($explosionRows !== []) {
        $sections[] = ['title' => 'Explosion', 'rows' => $explosionRows];
    }

    if ($damageRows !== []) {
        $sections[] = ['title' => 'Damage Breakdown', 'rows' => $damageRows];
    }
@endphp

<x-data-card title="Bomb" :sections="$sections" />
