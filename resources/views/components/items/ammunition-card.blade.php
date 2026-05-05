@use('App\Support\Format')
@props([
    'ammunition',
])

@php
    $uuid = data_get($ammunition, 'uuid');
    $speed = data_get($ammunition, 'speed');
    $lifetime = data_get($ammunition, 'lifetime');
    $range = data_get($ammunition, 'range');
    $size = data_get($ammunition, 'size');
    $capacity = data_get($ammunition, 'capacity');
    $initialCapacity = data_get($ammunition, 'initial_capacity');
    $bulletType = data_get($ammunition, 'bullet_type');
    $penetration = data_get($ammunition, 'penetration');
    $impactDamageMap = data_get($ammunition, 'impact_damage_map', []);
    $detonationDamageMap = data_get($ammunition, 'detonation_damage_map', []);
    $explosionRadius = data_get($ammunition, 'explosion_radius');
    $damageDropMinDistance = data_get($ammunition, 'damage_drop_min_distance');
    $damageDropPerMeter = data_get($ammunition, 'damage_drop_per_meter');
    $damageDropMinDamage = data_get($ammunition, 'damage_drop_min_damage');
    $bulletImpulseFalloff = data_get($ammunition, 'bullet_impulse_falloff');
    $bulletElectron = data_get($ammunition, 'bullet_electron');

    // Stats metrics (Size, Speed, Lifetime, Initial Capacity)
    $statsMetrics = array_values(array_filter([
        ['label' => 'Size', 'value' => $size, 'unit' => '', 'precision' => 0],
        ['label' => 'Speed', 'value' => $speed, 'unit' => 'm/s', 'precision' => 0],
        ['label' => 'Lifetime', 'value' => $lifetime, 'unit' => 's', 'precision' => 2],
        ['label' => 'Initial Capacity', 'value' => $initialCapacity, 'unit' => '', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));

    // Penetration metrics
    $penetrationMetrics = array_values(array_filter([
        ['label' => 'Base Distance', 'value' => data_get($penetration, 'base_distance'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Near Radius', 'value' => data_get($penetration, 'near_radius'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Far Radius', 'value' => data_get($penetration, 'far_radius'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Angle', 'value' => data_get($penetration, 'angle'), 'unit' => 'deg', 'precision' => 1],
    ], static fn (array $m): bool => $m['value'] !== null));

    // Damage breakdowns — 6 damage types
    $damageTypes = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun'];

    $impactBreakdown = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => 'Damage ' . Str::headline($type),
            'value' => data_get($impactDamageMap, $type),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    $detonationBreakdown = array_values(array_filter(
        collect($damageTypes)->map(fn (string $type): array => [
            'label' => Str::headline($type),
            'value' => data_get($detonationDamageMap, $type),
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    // Damage drop — 7 keys (6 types + total), 3 separate metric arrays
    // Filter out zero values
    $damageDropKeys = ['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun', 'total'];

    $damageDropMinDistMetrics = array_values(array_filter(
        collect($damageDropKeys)->map(fn (string $key): array => [
            'label' => Str::headline($key),
            'value' => data_get($damageDropMinDistance, $key),
            'unit' => 'm',
            'precision' => 0,
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

$damageDropPerMeterMetrics = array_values(array_filter(
    collect($damageDropKeys)->map(fn (string $key): array => [
        'label' => Str::headline($key),
        'value' => (data_get($damageDropPerMeter, $key) !== null) ? data_get($damageDropPerMeter, $key) * 100 : null,
        'unit' => '%',
        'precision' => 2,
    ])->all(),
    static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
));

    $damageDropMinDamageMetrics = array_values(array_filter(
        collect($damageDropKeys)->map(fn (string $key): array => [
            'label' => Str::headline($key),
            'value' => data_get($damageDropMinDamage, $key),
            'unit' => '',
            'precision' => 0,
        ])->all(),
        static fn (array $m): bool => $m['value'] !== null && $m['value'] > 0,
    ));

    // Explosion radius — special case with fmt_range
    $hasExplosionRadius = is_array($explosionRadius)
        && (data_get($explosionRadius, 'min') !== null || data_get($explosionRadius, 'max') !== null);

    // Bullet impulse falloff metrics
    $impulseFalloffMetrics = array_values(array_filter([
        ['label' => 'Min Distance', 'value' => data_get($bulletImpulseFalloff, 'min_distance'), 'unit' => '', 'precision' => 0],
        ['label' => 'Drop Falloff', 'value' => data_get($bulletImpulseFalloff, 'drop_falloff'), 'unit' => '', 'precision' => 0],
        ['label' => 'Max Falloff', 'value' => data_get($bulletImpulseFalloff, 'max_falloff'), 'unit' => '', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));

    // Bullet electron metrics
    $bulletElectronMetrics = array_values(array_filter([
        ['label' => 'Jump Range', 'value' => data_get($bulletElectron, 'jump_range'), 'unit' => 'm', 'precision' => 0],
        ['label' => 'Maximum Jumps', 'value' => data_get($bulletElectron, 'maximum_jumps'), 'unit' => '', 'precision' => 0],
    ], static fn (array $m): bool => $m['value'] !== null));
@endphp

<div {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Ammunition</h2>

        <x-dl-container>
            <x-slot:head>
                @if ($range !== null)
                    <x-dt-dd label="Range">{{ Format::valueWithUnit($range, 'm', 0) }}</x-dt-dd>
                @endif
                @if ($capacity !== null)
                    <x-dt-dd label="Capacity">{{ Format::numberOrDash($capacity, 0) }}</x-dt-dd>
                @endif
                @foreach ($impactBreakdown as $metric)
                    <x-dt-dd :label="$metric['label']">{{ Format::valueWithUnit($metric['value'], '', 0) }}</x-dt-dd>
                @endforeach
            </x-slot:head>
            <x-dl-section title="Penetration">
                @foreach ($penetrationMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Detonation Damage">
                @foreach ($detonationBreakdown as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], '', 0) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            @if ($hasExplosionRadius)
                <x-dl-section title="Explosion Radius">
                    <x-dt-dd label="Radius">
                        {{ Format::range(data_get($explosionRadius, 'min'), data_get($explosionRadius, 'max'), 'm', 0) }}
                    </x-dt-dd>
                </x-dl-section>
            @endif

            <x-dl-section title="Damage Drop: Min Distance">
                @foreach ($damageDropMinDistMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Damage Drop: Per Meter">
                @foreach ($damageDropPerMeterMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Damage Drop: Min Damage">
                @foreach ($damageDropMinDamageMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Bullet Impulse Falloff">
                @foreach ($impulseFalloffMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Bullet Electron">
                @foreach ($bulletElectronMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            <x-dl-section title="Stats">
                @foreach ($statsMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        @if ($metric['unit'] !== '')
                            {{ Format::valueWithUnit($metric['value'], $metric['unit'], $metric['precision']) }}
                        @else
                            {{ Format::numberOrDash($metric['value'], $metric['precision']) }}
                        @endif
                    </x-dt-dd>
                @endforeach
            </x-dl-section>
        </x-dl-container>
    </div>
</div>
