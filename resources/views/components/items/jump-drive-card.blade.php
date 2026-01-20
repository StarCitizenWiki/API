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
    $hasTravelTime = is_array($travelTime10GM) && array_filter($travelTime10GM, fn($v) => $v !== null);

    $thermalDraw = data_get($jumpDrive, 'thermal_energy_draw', []);
    $thermalPreRampUp = data_get($thermalDraw, 'pre_ramp_up');
    $thermalRampUp = data_get($thermalDraw, 'ramp_up');
    $thermalInFlight = data_get($thermalDraw, 'in_flight');
    $thermalRampDown = data_get($thermalDraw, 'ramp_down');
    $thermalPostRampDown = data_get($thermalDraw, 'post_ramp_down');
    $hasThermalDraw = is_array($thermalDraw) && array_filter($thermalDraw, fn($v) => $v !== null);

    $standardJump = data_get($jumpDrive, 'standard_jump', []);
    $hasStandardJump = is_array($standardJump) && array_filter($standardJump, fn($v) => $v !== null);

    $splineJump = data_get($jumpDrive, 'spline_jump', []);
    $hasSplineJump = is_array($splineJump) && array_filter($splineJump, fn($v) => $v !== null);

    $modes = data_get($jumpDrive, 'modes', []);
    $hasModes = is_array($modes) && $modes !== [];
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="egg-fried" class="size-4 text-primary" />
            <span>Jump Drive Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($alignmentRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Alignment Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$alignmentRate, 2) }}</dd>
                </div>
            @endif
            @if ($alignmentDecayRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Alignment Decay Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$alignmentDecayRate, 2) }}</dd>
                </div>
            @endif
            @if ($tuningRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tuning Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$tuningRate, 2) }}</dd>
                </div>
            @endif
            @if ($tuningDecayRate !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tuning Decay Rate</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$tuningDecayRate, 2) }}</dd>
                </div>
            @endif
            @if ($fuelUsageEfficiencyMultiplier !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel Usage Efficiency Multiplier</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)$fuelUsageEfficiencyMultiplier, 2) }}</dd>
                </div>
            @endif
            @if ($hasTravelTime)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Travel Time (10GM)</dt>
                    <dd class="text-sm font-medium">
                        @if ($travelTimeFormatted)
                            {{ $travelTimeFormatted }}
                        @elseif ($travelTimeSeconds !== null)
                            {{ number_format((float)$travelTimeSeconds, 2) }} s
                        @else
                            -
                        @endif
                    </dd>
                </div>
            @endif
        </dl>

        @if ($hasThermalDraw)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Thermal Energy Draw</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($thermalPreRampUp !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pre Ramp Up</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$thermalPreRampUp, 2) }} heat units/s</dd>
                            </div>
                        @endif
                        @if ($thermalRampUp !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ramp Up</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$thermalRampUp, 2) }} heat units/s</dd>
                            </div>
                        @endif
                        @if ($thermalInFlight !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">In Flight</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$thermalInFlight, 2) }} heat units/s</dd>
                            </div>
                        @endif
                        @if ($thermalRampDown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Ramp Down</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$thermalRampDown, 2) }} heat units/s</dd>
                            </div>
                        @endif
                        @if ($thermalPostRampDown !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Post Ramp Down</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$thermalPostRampDown, 2) }} heat units/s</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @if ($hasStandardJump)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Standard Jump Profile</div>
                <div class="collapse-content">
                    @include('components.items.quantum-drive-jump-profile', ['profile' => $standardJump])
                </div>
            </div>
        @endif

        @if ($hasSplineJump)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Spline Jump Profile</div>
                <div class="collapse-content">
                    @include('components.items.quantum-drive-jump-profile', ['profile' => $splineJump])
                </div>
            </div>
        @endif

        @if ($hasModes)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Jump Modes</div>
                <div class="collapse-content">
                    @foreach ($modes as $mode)
                        @php
                            $hasModeData = is_array($mode) && array_filter($mode, fn($v) => $v !== null);
                        @endphp
                        @if ($hasModeData)
                            <div class="mb-4">
                                @if (data_get($mode, 'type'))
                                    <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">
                                        {{ \Illuminate\Support\Str::headline(data_get($mode, 'type')) }}
                                    </div>
                                @endif
                                @include('components.items.quantum-drive-jump-profile', ['profile' => $mode])
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
