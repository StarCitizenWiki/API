@use('App\Support\Format')
@props([
    'armor',
 ])

@php
    $health = data_get($armor, 'health');

    $signalMultipliers = data_get($armor, 'signal_multiplier', []);
    $signalMetrics = array_values(array_filter([
        ['label' => 'Cross Section', 'change' => data_get($signalMultipliers, 'cross_section_change')],
        ['label' => 'Infrared', 'change' => data_get($signalMultipliers, 'infrared_change')],
        ['label' => 'Electromagnetic', 'change' => data_get($signalMultipliers, 'electromagnetic_change')],
    ], static fn (array $m): bool => $m['change'] !== null));

    $damageMultipliers = data_get($armor, 'damage_multiplier', []);
    $damageMetrics = array_values(array_filter([
        ['label' => 'Physical', 'value' => data_get($damageMultipliers, 'physical'), 'change' => data_get($damageMultipliers, 'physical_change')],
        ['label' => 'Energy', 'value' => data_get($damageMultipliers, 'energy'), 'change' => data_get($damageMultipliers, 'energy_change')],
        ['label' => 'Distortion', 'value' => data_get($damageMultipliers, 'distortion'), 'change' => data_get($damageMultipliers, 'distortion_change')],
        ['label' => 'Thermal', 'value' => data_get($damageMultipliers, 'thermal'), 'change' => data_get($damageMultipliers, 'thermal_change')],
        ['label' => 'Biochemical', 'value' => data_get($damageMultipliers, 'biochemical'), 'change' => data_get($damageMultipliers, 'biochemical_change')],
        ['label' => 'Stun', 'value' => data_get($damageMultipliers, 'stun'), 'change' => data_get($damageMultipliers, 'stun_change')],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 1));

    $resistanceMultipliers = data_get($armor, 'resistance_multiplier', []);
    $resistanceMetrics = array_values(array_filter([
        ['label' => 'Physical', 'value' => data_get($resistanceMultipliers, 'physical_change')],
        ['label' => 'Energy', 'value' => data_get($resistanceMultipliers, 'energy_change')],
        ['label' => 'Distortion', 'value' => data_get($resistanceMultipliers, 'distortion_change')],
        ['label' => 'Thermal', 'value' => data_get($resistanceMultipliers, 'thermal_change')],
        ['label' => 'Biochemical', 'value' => data_get($resistanceMultipliers, 'biochemical_change')],
        ['label' => 'Stun', 'value' => data_get($resistanceMultipliers, 'stun_change')],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

    $penetrationResist = data_get($armor, 'deflection', []);
    $penetrationMetrics = array_values(array_filter([
        ['label' => 'Base', 'value' => data_get($penetrationResist, 'base')],
        ['label' => 'Physical', 'value' => data_get($penetrationResist, 'physical')],
        ['label' => 'Energy', 'value' => data_get($penetrationResist, 'energy')],
        ['label' => 'Distortion', 'value' => data_get($penetrationResist, 'distortion')],
        ['label' => 'Thermal', 'value' => data_get($penetrationResist, 'thermal')],
        ['label' => 'Biochemical', 'value' => data_get($penetrationResist, 'biochemical')],
        ['label' => 'Stun', 'value' => data_get($penetrationResist, 'stun')],
    ], static fn (array $m): bool => $m['value'] !== null && $m['value'] != 0));

    $hasDeflection = $penetrationMetrics !== [];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Armor</h2>

        <x-dl-container>
            <x-slot:head>
                <x-dt-dd label="Health">{{ Format::valueWithUnit($health, 'HP', 0) }}</x-dt-dd>
            </x-slot:head>

            <x-dl-section title="Deflection">
                @foreach ($penetrationMetrics as $metric)
                    <x-dt-dd :label="$metric['label']">
                        {{ Format::numberOrDash($metric['value'], 2) }}
                    </x-dt-dd>
                @endforeach
            </x-dl-section>

            @if ($signalMetrics !== [])
                <x-dl-section title="Detection Signal">
                    @foreach ($signalMetrics as $metric)
                        <x-dt-dd :label="$metric['label']" :dd-class="Format::colorClass($metric['change'])">
                            {{ Format::valueWithUnit($metric['change'] * 100, '%', 1, sign: true) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif

            @if ($resistanceMetrics !== [])
                <x-dl-section title="Resistance">
                    @foreach ($resistanceMetrics as $metric)
                        <x-dt-dd :label="$metric['label']" :dd-class="Format::colorClass($metric['value'], true)">
                            {{ Format::valueWithUnit($metric['value'] * 100, '%', 1) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif

            @if ($damageMetrics !== [])
                <x-dl-section title="Damage Multipliers">
                    @foreach ($damageMetrics as $metric)
                        <x-dt-dd :label="$metric['label']" :dd-class="Format::colorClass($metric['change'])">
                            {{ Format::valueWithUnit($metric['change'] * 100, '%', 1, sign: true) }}
                        </x-dt-dd>
                    @endforeach
                </x-dl-section>
            @endif
        </x-dl-container>
    </div>
</div>
