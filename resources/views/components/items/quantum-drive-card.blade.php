@props([
    'quantumDrive',
 ])

@php
    $jumpRange = data_get($quantumDrive, 'jump_range');
    $disconnectRange = data_get($quantumDrive, 'disconnect_range');
    $quantumFuelRequirement = data_get($quantumDrive, 'quantum_fuel_requirement');
    $fuelRate = data_get($quantumDrive, 'fuel_rate');
    $fuelConsumption = data_get($quantumDrive, 'fuel_consumption_scu_per_gm');
    $fuelEfficiency = data_get($quantumDrive, 'fuel_efficiency');

    $travelTime10GM = data_get($quantumDrive, 'travel_time_10gm', []);
    $travelTimeFormatted = data_get($travelTime10GM, 'formatted');
    $travelTimeSeconds = data_get($travelTime10GM, 'seconds');

    $thermalDraw = data_get($quantumDrive, 'thermal_energy_draw', []);
    $thermalPreRampUp = data_get($thermalDraw, 'pre_ramp_up');
    $thermalRampUp = data_get($thermalDraw, 'ramp_up');
    $thermalInFlight = data_get($thermalDraw, 'in_flight');
    $thermalRampDown = data_get($thermalDraw, 'ramp_down');
    $thermalPostRampDown = data_get($thermalDraw, 'post_ramp_down');

    $standardJump = data_get($quantumDrive, 'standard_jump', []);
    $hasStandardJump = is_array($standardJump) && collect($standardJump)->filter(fn($v) => $v !== null)->isNotEmpty();

    $splineJump = data_get($quantumDrive, 'spline_jump', []);
    $hasSplineJump = is_array($splineJump) && collect($splineJump)->filter(fn($v) => $v !== null)->isNotEmpty();

    $modes = data_get($quantumDrive, 'modes', []);

    // Check if secondary section has any data
    $hasSecondaryData = $travelTimeSeconds !== null
        || $fuelEfficiency !== null
        || $thermalPreRampUp !== null
        || $thermalRampUp !== null
        || $thermalInFlight !== null
        || $thermalRampDown !== null
        || $thermalPostRampDown !== null;

    // Check if tertiary section has any data
    $hasTertiaryData = $hasStandardJump || $hasSplineJump || ($modes !== []);
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="atom" class="size-4 text-primary" />
            <span>Quantum Drive</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-2">
{{--            <div class="space-y-1">--}}
{{--                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Jump Range</dt>--}}
{{--                <dd class="text-sm font-medium">{{ fmt_value_with_unit($jumpRange, 'm', 0, compact: true) }}</dd>--}}
{{--            </div>--}}
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Disconnect Range</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($disconnectRange / 1000, 'km', 0, compact: true) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Consumption</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($fuelConsumption, 'SCU/GM', 8, compact: true) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Quantum Fuel Requirement</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($quantumFuelRequirement, 'SCU', 8, compact: true) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Rate</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit($fuelRate, '', 8, compact: true) }}</dd>
            </div>
        </dl>

        @if ($hasSecondaryData)
            <details id="performance-details" class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="true" aria-controls="performance-details-content">
                    Performance Details
                </summary>
                <div id="performance-details-content" class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($travelTimeSeconds !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Travel Time (10GM)</dt>
                                <dd class="text-sm font-medium">
                                    @if ($travelTimeFormatted)
                                        {{ $travelTimeFormatted }}
                                    @else
                                        {{ fmt_value_with_unit($travelTimeSeconds, 's', 2) }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($fuelEfficiency !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Efficiency</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($fuelEfficiency, 'GM/SCU', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasTertiaryData)
            <details id="jump-profiles" class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold" aria-expanded="false" aria-controls="jump-profiles-content">
                    Jump Profiles
                </summary>
                <div id="jump-profiles-content" class="collapse-content">
                    @if ($modes !== [])
                        @foreach ($modes as $mode)
                            @php
                                $hasModeData = is_array($mode) && collect($mode)->filter(fn($v) => $v !== null)->isNotEmpty();
                            @endphp
                            @if ($hasModeData)
                                <div class="mb-4">
                                    @if (data_get($mode, 'type'))
                                        <h4 class="text-xs font-semibold mb-2 mt-4 uppercase tracking-wide text-base-content/60">
                                            {{ \Illuminate\Support\Str::headline(data_get($mode, 'type')) }}
                                        </h4>
                                    @endif
                                    @include('components.items.quantum-drive-jump-profile', ['profile' => $mode])
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </details>
        @endif
    </div>
</div>
