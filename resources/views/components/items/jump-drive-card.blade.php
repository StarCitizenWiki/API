@props([
    'jumpDrive',
])

@php
    $alignmentRate = data_get($jumpDrive, 'alignment_rate');
    $alignmentDecayRate = data_get($jumpDrive, 'alignment_decay_rate');
    $tuningRate = data_get($jumpDrive, 'tuning_rate');
    $tuningDecayRate = data_get($jumpDrive, 'tuning_decay_rate');
    $fuelUsageEfficiencyMultiplier = data_get($jumpDrive, 'fuel_usage_efficiency_multiplier');

    $travelTime10GM = data_get($jumpDrive, 'travel_time_10gm', []);
    $travelTimeFormatted = data_get($travelTime10GM, 'formatted');
    $travelTimeSeconds = data_get($travelTime10GM, 'seconds');
    $hasTravelTime = is_array($travelTime10GM) && collect($travelTime10GM)->filter(fn($v) => $v !== null)->isNotEmpty();

    $thermalDraw = data_get($jumpDrive, 'thermal_energy_draw', []);
    $thermalPreRampUp = data_get($thermalDraw, 'pre_ramp_up');
    $thermalRampUp = data_get($thermalDraw, 'ramp_up');
    $thermalInFlight = data_get($thermalDraw, 'in_flight');
    $thermalRampDown = data_get($thermalDraw, 'ramp_down');
    $thermalPostRampDown = data_get($thermalDraw, 'post_ramp_down');
    $hasThermalDraw = is_array($thermalDraw) && collect($thermalDraw)->filter(fn($v) => $v !== null)->isNotEmpty();

    $standardJump = data_get($jumpDrive, 'standard_jump', []);
    $hasStandardJump = is_array($standardJump) && collect($standardJump)->filter(fn($v) => $v !== null)->isNotEmpty();

    $splineJump = data_get($jumpDrive, 'spline_jump', []);
    $hasSplineJump = is_array($splineJump) && collect($splineJump)->filter(fn($v) => $v !== null)->isNotEmpty();

    $modes = data_get($jumpDrive, 'modes', []);
    $hasModes = is_array($modes) && $modes !== [];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="egg-fried" class="size-4 text-primary" />
            <span>Jump Drive</span>
        </h2>

        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2">
            @if ($fuelUsageEfficiencyMultiplier !== null)
                <div class="space-y-1 col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Usage Efficiency</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($fuelUsageEfficiencyMultiplier, 'x', 2) }}</dd>
                </div>
            @endif
            @if ($alignmentRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Alignment Rate</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($alignmentRate, '', 2) }}</dd>
                </div>
            @endif
            @if ($alignmentDecayRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Alignment Decay Rate</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($alignmentDecayRate, '', 2) }}</dd>
                </div>
            @endif
            @if ($tuningRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tuning Rate</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($tuningRate, '', 2) }}</dd>
                </div>
            @endif
            @if ($tuningDecayRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tuning Decay Rate</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($tuningDecayRate, '', 2) }}</dd>
                </div>
            @endif

        </dl>

        @if ($hasTravelTime || $hasThermalDraw)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Jump Mechanics
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($hasTravelTime)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Travel Time (10GM)</dt>
                                <dd class="text-sm font-medium">
                                    @if ($travelTimeFormatted)
                                        {{ $travelTimeFormatted }}
                                    @elseif ($travelTimeSeconds !== null)
                                        {{ fmt_value_with_unit($travelTimeSeconds, 's', 2) }}
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($thermalPreRampUp !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pre Ramp Up</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($thermalPreRampUp, 'heat units/s', 2) }}</dd>
                            </div>
                        @endif
                        @if ($thermalRampUp !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ramp Up</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($thermalRampUp, 'heat units/s', 2) }}</dd>
                            </div>
                        @endif
                        @if ($thermalInFlight !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">In Flight</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($thermalInFlight, 'heat units/s', 2) }}</dd>
                            </div>
                        @endif
                        @if ($thermalRampDown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ramp Down</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($thermalRampDown, 'heat units/s', 2) }}</dd>
                            </div>
                        @endif
                        @if ($thermalPostRampDown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Post Ramp Down</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($thermalPostRampDown, 'heat units/s', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        @if ($hasStandardJump || $hasSplineJump || $hasModes)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Jump Profiles
                </summary>
                <div class="collapse-content">
                    @if ($hasStandardJump)
                        <h4 class="text-xs font-semibold mb-2 uppercase tracking-wide text-base-content/60">Standard Jump Profile</h4>
                        @include('components.items.quantum-drive-jump-profile', ['profile' => $standardJump])
                    @endif

                    @if ($hasSplineJump)
                        <h4 class="text-xs font-semibold mb-2 mt-4 uppercase tracking-wide text-base-content/60">Spline Jump Profile</h4>
                        @include('components.items.quantum-drive-jump-profile', ['profile' => $splineJump])
                    @endif

                    @if ($hasModes)
                        <h4 class="text-xs font-semibold mb-2 mt-4 uppercase tracking-wide text-base-content/60">Jump Modes</h4>
                        @foreach ($modes as $mode)
                            @php
                                $hasModeData = is_array($mode) && collect($mode)->filter(fn($v) => $v !== null)->isNotEmpty();
                            @endphp
                            @if ($hasModeData)
                                <div class="mb-4">
                                    @if (data_get($mode, 'type'))
                                        <h5 class="text-xs font-semibold mb-2">
                                            {{ \Illuminate\Support\Str::headline(data_get($mode, 'type')) }}
                                        </h5>
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
