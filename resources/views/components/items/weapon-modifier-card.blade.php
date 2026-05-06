@use('App\Support\Format')
@props([
    'weaponModifier',
])

@php
    $activateOnAttach = data_get($weaponModifier, 'activate_on_attach');
    $ignoreWear = data_get($weaponModifier, 'ignore_wear');

    // Base change metrics — only render _change when both _multiplier and _change exist
    $base = data_get($weaponModifier, 'base', []);
    $baseMetrics = collect([
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
            'value' => $m['value'] * 100,
            'invert' => $m['invert'],
        ])->values()->all();

    // Recoil change metrics
    $recoil = data_get($weaponModifier, 'recoil', []);
    $recoilMetrics = collect([
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
            'value' => $m['value'] * 100,
            'invert' => $m['invert'],
        ])->values()->all();

    // Spread change metrics
    $spread = data_get($weaponModifier, 'spread', []);
    $spreadMetrics = collect([
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
            'value' => $m['value'] * 100,
            'invert' => $m['invert'],
        ])->values()->all();

    // Aim metrics — mixed: zoom_time is a change metric; others are standalone
    $aim = data_get($weaponModifier, 'aim', []);
    $aimChangeMetrics = collect([
        ['key' => 'zoom_time', 'label' => 'Zoom Time', 'invert' => false],
    ])->map(fn (array $m): array => [
        'label' => $m['label'],
        'value' => data_get($aim, $m['key'] . '_change'),
        'has_multiplier' => data_get($aim, $m['key'] . '_scale') !== null,
        'invert' => $m['invert'],
    ])->filter(fn (array $m): bool => $m['has_multiplier'] && $m['value'] !== null && $m['value'] != 0)
        ->map(fn (array $m): array => [
            'label' => $m['label'],
            'value' => $m['value'] * 100,
            'invert' => $m['invert'],
        ])->values()->all();

    $aimStandaloneMetrics = [
        ['label' => 'Zoom Scale', 'value' => data_get($aim, 'zoom_scale'), 'unit' => '', 'precision' => 2],
        ['label' => 'Second Zoom Scale', 'value' => data_get($aim, 'second_zoom_scale'), 'unit' => '', 'precision' => 2],
        ['label' => 'Hide Weapon In ADS', 'value' => data_get($aim, 'hide_weapon_in_ads') !== null ? (data_get($aim, 'hide_weapon_in_ads') ? 'Yes' : 'No') : null, 'unit' => '', 'precision' => 0],
        ['label' => 'F-Stop', 'value' => data_get($aim, 'fstop_multiplier'), 'unit' => '', 'precision' => 2],
    ];

    $aimMetrics = array_merge($aimChangeMetrics, $aimStandaloneMetrics);

    // Regen metrics — standalone multipliers, no _change pairs
    $regen = data_get($weaponModifier, 'regen', []);
    $regenMetrics = array_values(array_filter([
        ['label' => 'Power Ratio', 'value' => data_get($regen, 'power_ratio_multiplier'), 'unit' => '', 'precision' => 2],
        ['label' => 'Max Ammo Load', 'value' => data_get($regen, 'max_ammo_load_multiplier'), 'unit' => '', 'precision' => 2],
        ['label' => 'Max Regen/sec', 'value' => data_get($regen, 'max_regen_per_sec_multiplier'), 'unit' => '', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

    // Salvage metrics — standalone, no _change pairs
    $salvage = data_get($weaponModifier, 'salvage', []);
    $salvageMetrics = array_values(array_filter([
        ['label' => 'Salvage Speed', 'value' => data_get($salvage, 'salvage_speed_multiplier'), 'unit' => '', 'precision' => 2],
        ['label' => 'Radius', 'value' => data_get($salvage, 'radius_multiplier'), 'unit' => '', 'precision' => 2],
        ['label' => 'Extraction Efficiency', 'value' => data_get($salvage, 'extraction_efficiency'), 'unit' => '', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

    // Zeroing metrics — absolute values with units
    $zeroing = data_get($weaponModifier, 'zeroing', []);
    $zeroingMetrics = array_values(array_filter([
        ['label' => 'Default Range', 'value' => data_get($zeroing, 'default_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Max Range', 'value' => data_get($zeroing, 'max_range'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Range Increment', 'value' => data_get($zeroing, 'range_increment'), 'unit' => 'm', 'precision' => 2],
        ['label' => 'Auto Zeroing Time', 'value' => data_get($zeroing, 'auto_zeroing_time'), 'unit' => 's', 'precision' => 2],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

@endphp

<x-item-card title="Weapon Modifier">
    <x-dl-container>
        <x-slot:head>
            <x-dt-dd label="Activate On Attach" :value="$activateOnAttach">{{ $activateOnAttach ? 'Yes' : 'No' }}</x-dt-dd>
            <x-dt-dd label="Ignore Wear" :value="$ignoreWear">{{ $ignoreWear ? 'Yes' : 'No' }}</x-dt-dd>
        </x-slot:head>

        <x-dl-section title="Base">
            @foreach ($baseMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    <span class="{{ Format::colorClass($metric['value'], $metric['invert']) }}">{{ Format::valueWithUnit($metric['value'], '%', 1) }}</span>
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Recoil">
            @foreach ($recoilMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    <span class="{{ Format::colorClass($metric['value'], $metric['invert']) }}">{{ Format::valueWithUnit($metric['value'], '%', 1) }}</span>
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Spread">
            @foreach ($spreadMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    <span class="{{ Format::colorClass($metric['value'], $metric['invert']) }}">{{ Format::valueWithUnit($metric['value'], '%', 1) }}</span>
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Aim">
            @foreach ($aimMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    @if (isset($metric['invert']))
                        <span class="{{ Format::colorClass($metric['value'], $metric['invert']) }}">{{ Format::valueWithUnit($metric['value'], '%', 1) }}</span>
                    @elseif ($metric['label'] === 'Hide Weapon In ADS')
                        {{ $metric['value'] }}
                    @else
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    @endif
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Regen">
            @foreach ($regenMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Salvage">
            @foreach ($salvageMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>

        <x-dl-section title="Zeroing">
            @foreach ($zeroingMetrics as $metric)
                <x-dt-dd :label="$metric['label']" :value="$metric['value'] ?? null">
                    {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                </x-dt-dd>
            @endforeach
        </x-dl-section>
    </x-dl-container>
</x-item-card>
