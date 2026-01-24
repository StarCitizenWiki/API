@props(['vehicle'])

@php
    $sizeClass = data_get($vehicle, 'size_class');
    $massTotal = data_get($vehicle, 'mass_total', data_get($vehicle, 'mass'));
    $massLoadout = data_get($vehicle, 'mass_loadout');

    $dimension = data_get($vehicle, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');

    $cargoCapacity = data_get($vehicle, 'cargo_capacity');

    $crew = data_get($vehicle, 'crew', []);
    $crewMin = data_get($crew, 'min');
    $crewMax = data_get($crew, 'max');

    $speed = data_get($vehicle, 'speed', []);
    $scmSpeed = data_get($speed, 'scm');
    $maxSpeed = data_get($speed, 'max');

    $health = data_get($vehicle, 'health');

    $shield = data_get($vehicle, 'shield', []);
    $shieldHp = data_get($shield, 'hp');

    $signature = data_get($vehicle, 'signature', []);
    $irShields = data_get($signature, 'ir_shields');
    $emShields = data_get($signature, 'em_shields');

    $crossSection = data_get($vehicle, 'cross_section', []);
    $csLength = data_get($crossSection, 'length');
    $csWidth = data_get($crossSection, 'width');
    $csHeight = data_get($crossSection, 'height');
    $csMax = max($csLength ?? 0, $csWidth ?? 0, $csHeight ?? 0);

    $quantum = data_get($vehicle, 'quantum', []);
    $quantumSpeed = data_get($quantum, 'quantum_speed');

    $isSpaceship = data_get($vehicle, 'is_spaceship');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="heroicon-o-chart-bar" class="size-4 text-primary" />
            <span>Core Specifications</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Size Class</dt>
                <dd class="text-sm font-medium">
                    @if ($sizeClass)
                        <span class="badge badge-ghost">{{ $sizeClass }}</span>
                    @else
                        -
                    @endif
                </dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass</dt>
                <dd class="text-sm font-medium">
                    {{ $massTotal ? number_format($massTotal / 1000, 2) . ' t' : '-' }}
                    @if ($massLoadout)
                        <span class="text-xs text-base-content/60" title="Loadout mass: {{ number_format($massLoadout, 0) }} kg">
                            ({{ number_format($massLoadout / 1000, 2) }} t loadout)
                        </span>
                    @endif
                </dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Dimensions</dt>
                <dd class="text-sm font-medium">
                    @if ($length || $width || $height)
                        {{ number_format($length ?? 0, 1) }}m × {{ number_format($width ?? 0, 1) }}m × {{ number_format($height ?? 0, 1) }}m
                    @else
                        -
                    @endif
                </dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cargo</dt>
                <dd class="text-sm font-medium">{{ $cargoCapacity ? $cargoCapacity . ' SCU' : '-' }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Crew</dt>
                <dd class="text-sm font-medium">
                    @if ($crewMin || $crewMax)
                        {{ $crewMin ?? '-' }} / {{ $crewMax ?? '-' }}
                    @else
                        -
                    @endif
                </dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">SCM Speed</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($scmSpeed, 'm/s', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                    @if ($isSpaceship)
                        NAV / Afterburner
                    @else
                        Max Speed
                    @endif
                </dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($maxSpeed, 'm/s', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Health</dt>
                <dd class="text-sm font-medium">{{ $health ? number_format($health, 0) . ' HP' : '-' }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Shield HP</dt>
                <dd class="text-sm font-medium">{{ $shieldHp ? number_format($shieldHp, 0) . ' HP' : '-' }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">IR Signature</dt>
                <dd class="text-sm font-medium">{{ $irShields ? number_format($irShields, 0) : '-' }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">EM Signature</dt>
                <dd class="text-sm font-medium">{{ $emShields ? number_format($emShields, 0) : '-' }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section</dt>
                <dd class="text-sm font-medium">{{ $csMax > 0 ? number_format($csMax, 2) : '-' }}</dd>
            </div>

            @if ($quantumSpeed)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Quantum Speed</dt>
                    <dd class="text-sm font-medium">{{ fmt_compact($quantumSpeed, 0) }} m/s</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
