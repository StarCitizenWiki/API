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
    <section {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
        <div class="card-body gap-4">
            <h2 class="card-title text-base">Armor</h2>

            <div class="grid gap-12 lg:grid-cols-3">
                @if ($health !== null || $hasDeflection)
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-base-content">Health & Deflection</h3>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2">

                            @if ($health !== null)
                                <dt class="text-sm text-emphasis">Health</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">
                                    {{ fmt_or_dash($health) }} <span class="text-xs text-muted">HP</span>
                                </dd>
                            @endif

                            @if ($deflectionPhysical !== null)
                                <dt class="text-sm text-emphasis">Physical Def.</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">{{ fmt_or_dash($deflectionPhysical) }}</dd>
                            @endif

                            @if ($deflectionEnergy !== null)
                                <dt class="text-sm text-emphasis">Energy Def.</dt>
                                <dd class="text-right text-sm font-semibold text-base-content">{{ fmt_or_dash($deflectionEnergy) }}</dd>
                            @endif
                        </dl>
                    </section>
                @endif

                @if ($damageRows !== [])
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-base-content">Damage Multipliers</h3>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2">

                            @foreach ($damageRows as $row)
                                <dt class="text-sm text-emphasis">{{ $row['label'] }}</dt>
                                <dd class="text-right text-sm font-semibold text-base-content {{ color_class($row['value']-1) }}">{{ fmt_signed_percent($row['value']) }}</dd>
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($signalRows !== [])
                    <section class="space-y-4">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold text-base-content">Signal Multipliers</h3>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-3 gap-y-2">

                            @foreach ($signalRows as $row)
                                <dt class="text-sm text-emphasis">{{ $row['label'] }}</dt>
                                <dd class="text-right text-sm font-semibold text-base-content {{ color_class($row['value']-1) }}">{{ fmt_signed_percent($row['value']) }}</dd>
                            @endforeach
                        </dl>
                    </section>
                @endif
            </div>
        </div>
    </section>
@endif
