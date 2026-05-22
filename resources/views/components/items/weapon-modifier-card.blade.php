@use('App\Support\Format')
@props([
    'weaponModifier',
])

@php
    $sections = [];

    $activateOnAttach = data_get($weaponModifier, 'activate_on_attach');
    $ignoreWear = data_get($weaponModifier, 'ignore_wear');

    $headRows = array_values(array_filter([
        ['label' => 'Activate On Attach', 'value' => $activateOnAttach !== null ? ($activateOnAttach ? 'Yes' : 'No') : null],
        ['label' => 'Ignore Wear', 'value' => $ignoreWear !== null ? ($ignoreWear ? 'Yes' : 'No') : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($headRows !== []) {
        $sections[] = ['title' => 'Info', 'rows' => $headRows];
    }

    // Base
    $base = data_get($weaponModifier, 'base', []);
    $baseRows = collect([
        ['key' => 'muzzle_flash', 'label' => 'Muzzle Flash', 'invert' => false],
        ['key' => 'fire_rate', 'label' => 'Fire Rate', 'invert' => true],
        ['key' => 'damage', 'label' => 'Damage', 'invert' => true],
        ['key' => 'projectile_speed', 'label' => 'Projectile Speed', 'invert' => true],
        ['key' => 'ammo_cost', 'label' => 'Ammo Cost', 'invert' => false],
        ['key' => 'heat_generation', 'label' => 'Heat Generation', 'invert' => false],
        ['key' => 'sound_radius', 'label' => 'Sound Radius', 'invert' => false],
        ['key' => 'charge_time', 'label' => 'Charge Time', 'invert' => false],
    ])->map(fn (array $m): array => [
        'label' => $m['label'],
        'value' => data_get($base, $m['key'] . '_change'),
        'has_multiplier' => data_get($base, $m['key'] . '_multiplier') !== null,
        'invert' => $m['invert'],
    ])->filter(fn (array $m): bool => $m['has_multiplier'] && $m['value'] !== null && $m['value'] != 0)
        ->map(fn (array $m): array => [
            'label' => $m['label'],
            'value' => Format::valueWithUnit($m['value'] * 100, '%', 1),
            'class' => Format::colorClass($m['value'] * 100, $m['invert']),
        ])->values()->all();

    if ($baseRows !== []) {
        $sections[] = ['title' => 'Base', 'rows' => $baseRows];
    }

    // Recoil
    $recoil = data_get($weaponModifier, 'recoil', []);
    $recoilRows = collect([
        ['key' => 'decay', 'label' => 'Decay', 'invert' => true],
        ['key' => 'multiplier', 'label' => 'Randomness', 'invert' => false],
    ])->map(fn (array $m): array => [
        'label' => $m['label'],
        'value' => data_get($recoil, $m['key'] . '_change'),
        'has_multiplier' => data_get($recoil, $m['key'] . '_multiplier') !== null,
        'invert' => $m['invert'],
    ])->filter(fn (array $m): bool => $m['has_multiplier'] && $m['value'] !== null && $m['value'] != 0)
        ->map(fn (array $m): array => [
            'label' => $m['label'],
            'value' => Format::valueWithUnit($m['value'] * 100, '%', 1),
            'class' => Format::colorClass($m['value'] * 100, $m['invert']),
        ])->values()->all();

    if ($recoilRows !== []) {
        $sections[] = ['title' => 'Recoil', 'rows' => $recoilRows];
    }

    // Spread
    $spread = data_get($weaponModifier, 'spread', []);
    $spreadRows = collect([
        ['key' => 'min', 'label' => 'Min Spread', 'invert' => false],
        ['key' => 'max', 'label' => 'Max Spread', 'invert' => false],
        ['key' => 'first_attack', 'label' => 'First Attack', 'invert' => false],
        ['key' => 'per_attack', 'label' => 'Per Attack', 'invert' => false],
        ['key' => 'decay', 'label' => 'Decay', 'invert' => true],
    ])->map(fn (array $m): array => [
        'label' => $m['label'],
        'value' => data_get($spread, $m['key'] . '_change'),
        'has_multiplier' => data_get($spread, $m['key'] . '_multiplier') !== null,
        'invert' => $m['invert'],
    ])->filter(fn (array $m): bool => $m['has_multiplier'] && $m['value'] !== null && $m['value'] != 0)
        ->map(fn (array $m): array => [
            'label' => $m['label'],
            'value' => Format::valueWithUnit($m['value'] * 100, '%', 1),
            'class' => Format::colorClass($m['value'] * 100, $m['invert']),
        ])->values()->all();

    if ($spreadRows !== []) {
        $sections[] = ['title' => 'Spread', 'rows' => $spreadRows];
    }

    // Aim
    $aim = data_get($weaponModifier, 'aim', []);
    $aimRows = [];

    $aimChangeRows = collect([
        ['key' => 'zoom_time', 'label' => 'Zoom Time', 'invert' => false],
    ])->map(fn (array $m): array => [
        'label' => $m['label'],
        'value' => data_get($aim, $m['key'] . '_change'),
        'has_multiplier' => data_get($aim, $m['key'] . '_scale') !== null,
        'invert' => $m['invert'],
    ])->filter(fn (array $m): bool => $m['has_multiplier'] && $m['value'] !== null && $m['value'] != 0)
        ->map(fn (array $m): array => [
            'label' => $m['label'],
            'value' => Format::valueWithUnit($m['value'] * 100, '%', 1),
            'class' => Format::colorClass($m['value'] * 100, $m['invert']),
        ])->values()->all();

    $aimStandaloneRows = array_values(array_filter([
        ['label' => 'Zoom Scale', 'value' => data_get($aim, 'zoom_scale') !== null ? Format::valueWithUnit(data_get($aim, 'zoom_scale'), '', 2) : null],
        ['label' => 'Second Zoom Scale', 'value' => data_get($aim, 'second_zoom_scale') !== null ? Format::valueWithUnit(data_get($aim, 'second_zoom_scale'), '', 2) : null],
        ['label' => 'Hide Weapon In ADS', 'value' => data_get($aim, 'hide_weapon_in_ads') !== null ? (data_get($aim, 'hide_weapon_in_ads') ? 'Yes' : 'No') : null],
        ['label' => 'F-Stop', 'value' => data_get($aim, 'fstop_multiplier') !== null ? Format::valueWithUnit(data_get($aim, 'fstop_multiplier'), '', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    $aimRows = array_merge($aimChangeRows, $aimStandaloneRows);

    if ($aimRows !== []) {
        $sections[] = ['title' => 'Aim', 'rows' => $aimRows];
    }

    // Regen
    $regen = data_get($weaponModifier, 'regen', []);
    $regenRows = array_values(array_filter([
        ['label' => 'Power Ratio', 'value' => data_get($regen, 'power_ratio_multiplier') !== null ? Format::valueWithUnit(data_get($regen, 'power_ratio_multiplier'), '', 2) : null],
        ['label' => 'Max Ammo Load', 'value' => data_get($regen, 'max_ammo_load_multiplier') !== null ? Format::valueWithUnit(data_get($regen, 'max_ammo_load_multiplier'), '', 2) : null],
        ['label' => 'Max Regen/sec', 'value' => data_get($regen, 'max_regen_per_sec_multiplier') !== null ? Format::valueWithUnit(data_get($regen, 'max_regen_per_sec_multiplier'), '', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($regenRows !== []) {
        $sections[] = ['title' => 'Regen', 'rows' => $regenRows];
    }

    // Salvage
    $salvage = data_get($weaponModifier, 'salvage', []);
    $salvageRows = array_values(array_filter([
        ['label' => 'Salvage Speed', 'value' => data_get($salvage, 'salvage_speed_multiplier') !== null ? Format::valueWithUnit(data_get($salvage, 'salvage_speed_multiplier'), '', 2) : null],
        ['label' => 'Radius', 'value' => data_get($salvage, 'radius_multiplier') !== null ? Format::valueWithUnit(data_get($salvage, 'radius_multiplier'), '', 2) : null],
        ['label' => 'Extraction Efficiency', 'value' => data_get($salvage, 'extraction_efficiency') !== null ? Format::valueWithUnit(data_get($salvage, 'extraction_efficiency'), '', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($salvageRows !== []) {
        $sections[] = ['title' => 'Salvage', 'rows' => $salvageRows];
    }

    // Zeroing
    $zeroing = data_get($weaponModifier, 'zeroing', []);
    $zeroingRows = array_values(array_filter([
        ['label' => 'Default Range', 'value' => data_get($zeroing, 'default_range') !== null ? Format::valueWithUnit(data_get($zeroing, 'default_range'), 'm', 2) : null],
        ['label' => 'Max Range', 'value' => data_get($zeroing, 'max_range') !== null ? Format::valueWithUnit(data_get($zeroing, 'max_range'), 'm', 2) : null],
        ['label' => 'Range Increment', 'value' => data_get($zeroing, 'range_increment') !== null ? Format::valueWithUnit(data_get($zeroing, 'range_increment'), 'm', 2) : null],
        ['label' => 'Auto Zeroing Time', 'value' => data_get($zeroing, 'auto_zeroing_time') !== null ? Format::valueWithUnit(data_get($zeroing, 'auto_zeroing_time'), 's', 2) : null],
    ], static fn (array $row): bool => $row['value'] !== null));

    if ($zeroingRows !== []) {
        $sections[] = ['title' => 'Zeroing', 'rows' => $zeroingRows];
    }
@endphp

<x-data-card title="Weapon Modifier" :sections="$sections" />
