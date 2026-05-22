@use('App\Support\Format')
@php use Illuminate\Support\Str; @endphp
@props([
    'ammunition',
])

@php
    $sections = [];

    $range = data_get($ammunition, 'range');
    $capacity = data_get($ammunition, 'capacity');
    $size = data_get($ammunition, 'size');
    $speed = data_get($ammunition, 'speed');
    $lifetime = data_get($ammunition, 'lifetime');
    $initialCapacity = data_get($ammunition, 'initial_capacity');
    $penetration = data_get($ammunition, 'penetration');
    $impactDamageMap = data_get($ammunition, 'impact_damage_map', []);
    $detonationDamageMap = data_get($ammunition, 'detonation_damage_map', []);
    $explosionRadius = data_get($ammunition, 'explosion_radius');
    $damageDropMinDistance = data_get($ammunition, 'damage_drop_min_distance');
    $damageDropPerMeter = data_get($ammunition, 'damage_drop_per_meter');
    $damageDropMinDamage = data_get($ammunition, 'damage_drop_min_damage');
    $bulletImpulseFalloff = data_get($ammunition, 'bullet_impulse_falloff');
    $bulletElectron = data_get($ammunition, 'bullet_electron');
    $damageTypes = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun'];

    // Head: Range, Capacity, Impact breakdown
    $headRows = [];
    if ($range !== null) {
        $headRows[] = ['label' => 'Range', 'value' => Format::valueWithUnit($range, 'm', 0)];
    }
    if ($capacity !== null) {
        $headRows[] = ['label' => 'Capacity', 'value' => Format::numberOrDash($capacity, 0)];
    }

    $impactBreakdown = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => 'Damage ' . Str::headline($type),
            'value' => data_get($impactDamageMap, $type),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    foreach ($impactBreakdown as $metric) {
        $headRows[] = ['label' => $metric['label'], 'value' => Format::valueWithUnit($metric['value'], '', 0)];
    }

    if ($headRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $headRows];
    }

    // Penetration
    $penetrationRows = array_values(array_filter([
        ['label' => 'Base Distance', 'value' => data_get($penetration, 'base_distance') !== null ? Format::valueWithUnit(data_get($penetration, 'base_distance'), 'm', 0) : null],
        ['label' => 'Near Radius', 'value' => data_get($penetration, 'near_radius') !== null ? Format::valueWithUnit(data_get($penetration, 'near_radius'), 'm', 0) : null],
        ['label' => 'Far Radius', 'value' => data_get($penetration, 'far_radius') !== null ? Format::valueWithUnit(data_get($penetration, 'far_radius'), 'm', 0) : null],
        ['label' => 'Angle', 'value' => data_get($penetration, 'angle') !== null ? Format::valueWithUnit(data_get($penetration, 'angle'), 'deg', 1) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($penetrationRows !== []) {
        $sections[] = ['title' => 'Penetration', 'rows' => $penetrationRows];
    }

    // Detonation Damage
    $detonationBreakdown = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => Str::headline($type),
            'value' => data_get($detonationDamageMap, $type),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    $detonationRows = [];
    foreach ($detonationBreakdown as $metric) {
        $detonationRows[] = ['label' => $metric['label'], 'value' => Format::valueWithUnit($metric['value'], '', 0)];
    }

    if ($detonationRows !== []) {
        $sections[] = ['title' => 'Detonation Damage', 'rows' => $detonationRows];
    }

    // Explosion Radius
    $expRadiusMin = data_get($explosionRadius, 'min');
    $expRadiusMax = data_get($explosionRadius, 'max');
    if ($expRadiusMin !== null || $expRadiusMax !== null) {
        $sections[] = ['title' => 'Explosion Radius', 'rows' => [
            ['label' => 'Radius', 'value' => Format::range($expRadiusMin, $expRadiusMax, 'm', 0)],
        ]];
    }

    // Damage Drop: Min Distance
    $damageDropKeys = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun', 'total'];

    $dropMinDistRows = array_values(array_filter(
        collect($damageDropKeys)->map(fn (string $key): ?array => (data_get($damageDropMinDistance, $key) !== null && data_get($damageDropMinDistance, $key) > 0)
            ? ['label' => Str::headline($key), 'value' => Format::valueWithUnit(data_get($damageDropMinDistance, $key), 'm', 0)]
            : null)->all(),
        static fn (?array $row): bool => $row !== null,
    ));

    if ($dropMinDistRows !== []) {
        $sections[] = ['title' => 'Damage Drop: Min Distance', 'rows' => $dropMinDistRows];
    }

    // Damage Drop: Per Meter
    $dropPerMeterRows = array_values(array_filter(
        collect($damageDropKeys)->map(fn (string $key): ?array => (data_get($damageDropPerMeter, $key) !== null && data_get($damageDropPerMeter, $key) > 0)
            ? ['label' => Str::headline($key), 'value' => Format::valueWithUnit(data_get($damageDropPerMeter, $key) * 100, '%', 2)]
            : null)->all(),
        static fn (?array $row): bool => $row !== null,
    ));

    if ($dropPerMeterRows !== []) {
        $sections[] = ['title' => 'Damage Drop: Per Meter', 'rows' => $dropPerMeterRows];
    }

    // Damage Drop: Min Damage
    $dropMinDmgRows = array_values(array_filter(
        collect($damageDropKeys)->map(fn (string $key): ?array => (data_get($damageDropMinDamage, $key) !== null && data_get($damageDropMinDamage, $key) > 0)
            ? ['label' => Str::headline($key), 'value' => Format::numberOrDash(data_get($damageDropMinDamage, $key), 0)]
            : null)->all(),
        static fn (?array $row): bool => $row !== null,
    ));

    if ($dropMinDmgRows !== []) {
        $sections[] = ['title' => 'Damage Drop: Min Damage', 'rows' => $dropMinDmgRows];
    }

    // Bullet Impulse Falloff
    $impulseRows = array_values(array_filter([
        ['label' => 'Min Distance', 'value' => data_get($bulletImpulseFalloff, 'min_distance') !== null ? Format::numberOrDash(data_get($bulletImpulseFalloff, 'min_distance'), 0) : null],
        ['label' => 'Drop Falloff', 'value' => data_get($bulletImpulseFalloff, 'drop_falloff') !== null ? Format::numberOrDash(data_get($bulletImpulseFalloff, 'drop_falloff'), 0) : null],
        ['label' => 'Max Falloff', 'value' => data_get($bulletImpulseFalloff, 'max_falloff') !== null ? Format::numberOrDash(data_get($bulletImpulseFalloff, 'max_falloff'), 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null && $row['value'] !== '-'));

    if ($impulseRows !== []) {
        $sections[] = ['title' => 'Bullet Impulse Falloff', 'rows' => $impulseRows];
    }

    // Bullet Electron
    $electronRows = array_values(array_filter([
        ['label' => 'Jump Range', 'value' => data_get($bulletElectron, 'jump_range') !== null ? Format::valueWithUnit(data_get($bulletElectron, 'jump_range'), 'm', 0) : null],
        ['label' => 'Maximum Jumps', 'value' => data_get($bulletElectron, 'maximum_jumps') !== null ? Format::numberOrDash(data_get($bulletElectron, 'maximum_jumps'), 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null && $row['value'] !== '-'));

    if ($electronRows !== []) {
        $sections[] = ['title' => 'Bullet Electron', 'rows' => $electronRows];
    }

    // Stats
    $statsRows = array_values(array_filter([
        ['label' => 'Size', 'value' => $size !== null ? Format::numberOrDash($size, 0) : null],
        ['label' => 'Speed', 'value' => $speed !== null ? Format::valueWithUnit($speed, 'm/s', 0) : null],
        ['label' => 'Lifetime', 'value' => $lifetime !== null ? Format::valueWithUnit($lifetime, 's', 2) : null],
        ['label' => 'Initial Capacity', 'value' => $initialCapacity !== null ? Format::numberOrDash($initialCapacity, 0) : null],
    ], static fn (array $row): bool => $row['value'] !== null && $row['value'] !== '-'));

    if ($statsRows !== []) {
        $sections[] = ['title' => 'Stats', 'rows' => $statsRows];
    }
@endphp

<x-data-card title="Ammunition" :sections="$sections" />
