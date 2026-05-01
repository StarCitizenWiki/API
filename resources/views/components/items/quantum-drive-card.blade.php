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
        <h2 class="card-title text-base">Quantum Drive</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
{{--            <div class="space-y-1">--}}
{{--                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Jump Range</dt>--}}
{{--                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($jumpRange, 'm', 0, compact: true) }}</dd>--}}
{{--            </div>--}}
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Disconnect Range</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($disconnectRange / 1000, 'km', 0, compact: true) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Fuel Consumption</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($fuelConsumption, 'SCU/GM', 8, compact: true) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Quantum Fuel Requirement</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($quantumFuelRequirement, 'SCU', 8, compact: true) }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Fuel Rate</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($fuelRate, '', 8, compact: true) }}</dd>
            </div>
        </dl>

        @if ($hasSecondaryData)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Performance Details
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($travelTimeSeconds !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Travel Time (10GM)</dt>
                                <dd class="text-sm font-semibold text-base-content">
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
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Fuel Efficiency</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($fuelEfficiency, 'GM/SCU', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        @if ($hasTertiaryData)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Jump Profiles
                </summary>
                    @if ($modes !== [])
                        @foreach ($modes as $mode)
                            @php
                                $hasModeData = is_array($mode) && collect($mode)->filter(fn($v) => $v !== null)->isNotEmpty();
                            @endphp
                            @if ($hasModeData)
                                <div class="mb-4">
                                    @if (data_get($mode, 'type'))
                                        <h4 class="text-xs font-medium mb-2 mt-4 uppercase tracking-wide text-muted">
                                            {{ \Illuminate\Support\Str::headline(data_get($mode, 'type')) }}
                                        </h4>
                                    @endif
                                    @include('components.items.quantum-drive-jump-profile', ['profile' => $mode])
                                </div>
                            @endif
                        @endforeach
                    @endif
            </details>
        @endif
    </div>
</div>
