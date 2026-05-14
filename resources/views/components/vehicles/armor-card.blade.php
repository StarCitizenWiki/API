@use('App\Support\Format')
@props(['vehicle'])

@php
    $armor = data_get($vehicle, 'armor', []);
    $health = data_get($armor, 'health');

    $deflectionPhysical = data_get($armor, 'deflection.physical');
    $deflectionEnergy = data_get($armor, 'deflection.energy');

    $resistanceMultipliers = data_get($armor, 'resistance_multipliers', []);
    $signalMultipliers = data_get($armor, 'signal_multipliers', []);

    $damageRows = array_values(array_filter([
        ['label' => 'Physical', 'value' => data_get($resistanceMultipliers, 'physical')],
        ['label' => 'Energy', 'value' => data_get($resistanceMultipliers, 'energy')],
        ['label' => 'Distortion', 'value' => data_get($resistanceMultipliers, 'distortion')],
    ], static fn (array $row): bool => $row['value'] !== null));

    $signalRows = array_values(array_filter([
        ['label' => 'EM', 'value' => data_get($signalMultipliers, 'electromagnetic')],
        ['label' => 'IR', 'value' => data_get($signalMultipliers, 'infrared')],
        ['label' => 'CS', 'value' => data_get($signalMultipliers, 'cross_section')],
    ], static fn (array $row): bool => $row['value'] !== null));

    $hasDeflection = $deflectionPhysical !== null || $deflectionEnergy !== null;
    $hasArmorData = $health !== null || $hasDeflection || $damageRows !== [] || $signalRows !== [];
@endphp

@if ($hasArmorData)
    <section {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Armor</h2>

            <div class="grid gap-12 grid-cols-1 lg:grid-cols-2 xl:grid-cols-3">
                @if ($health !== null || $hasDeflection)
                    <x-dl-section title="Health & Deflection">
                        @if ($health !== null)
                            <x-dt-dd label="Health">
                                {{ Format::numberOrDash($health) }} <span class="text-xs text-muted">HP</span>
                            </x-dt-dd>
                        @endif

                        @if ($deflectionPhysical !== null)
                            <x-dt-dd label="Physical Def.">
                                {{ Format::numberOrDash($deflectionPhysical) }}
                            </x-dt-dd>
                        @endif

                        @if ($deflectionEnergy !== null)
                            <x-dt-dd label="Energy Def.">
                                {{ Format::numberOrDash($deflectionEnergy) }}
                            </x-dt-dd>
                        @endif
                    </x-dl-section>
                @endif

                @if ($damageRows !== [])
                    <x-dl-section title="Damage Multipliers">
                        @foreach ($damageRows as $row)
                            <x-dt-dd :label="$row['label']">
                                <span class="{{ Format::colorClass($row['value'] - 1) }}">{{ Format::signedPercent($row['value']) }}</span>
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif

                @if ($signalRows !== [])
                    <x-dl-section title="Signal Multipliers">
                        @foreach ($signalRows as $row)
                            <x-dt-dd :label="$row['label']">
                                <span class="{{ Format::colorClass($row['value'] - 1) }}">{{ Format::signedPercent($row['value']) }}</span>
                            </x-dt-dd>
                        @endforeach
                    </x-dl-section>
                @endif
            </div>
        </div>
    </section>
@endif
