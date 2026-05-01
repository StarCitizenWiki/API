@props(['quantumInterdictionGenerator' => null])

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Quantum Interdiction Generator</h2>

        @php
            $jamming = data_get($quantumInterdictionGenerator, 'jamming', []);
            $pulse = data_get($quantumInterdictionGenerator, 'pulse', []);
            $powerFractions = data_get($quantumInterdictionGenerator, 'power_fractions', []);

            $hasJamming = is_array($jamming) && collect($jamming)->filter(fn($v) => $v !== null)->isNotEmpty();
            $hasPulse = is_array($pulse) && collect($pulse)->filter(fn($v) => $v !== null)->isNotEmpty();
            $hasPowerFractions = is_array($powerFractions) && collect($powerFractions)->filter(static fn($v) => $v !== null)->isNotEmpty();
        @endphp

        {{-- Primary Data (Always Visible) --}}
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Jamming Range</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($jamming, 'range'), 'm', 0) }}</dd>
            </div>

            <div class="space-y-1 col-span-2">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pulse Radius</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'radius'), 'm', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Charge Time</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'charge_time'), 's', 1) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Discharge Time</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'discharge_time'), 's', 1) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Cooldown Time</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'cooldown_time'), 's', 1) }}</dd>
            </div>

        </dl>

        @if ($hasPowerFractions || $hasJamming || $hasPulse)
            <details class="group">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Power / Disperse Data
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($hasPowerFractions)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Base Power Fraction</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($powerFractions, 'base'), '', 2) }}</dd>
                            </div>

                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pulse Power Fraction</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($powerFractions, 'pulse'), '', 2) }}</dd>
                            </div>

                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Jammer Power Fraction</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($powerFractions, 'jammer'), '', 2) }}</dd>
                            </div>
                        @endif

                        @if ($hasJamming)
                            @if (data_get($jamming, 'green_zone_check_range') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Jamming Green Zone Check Range</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($jamming, 'green_zone_check_range'), 'm', 0) }}</dd>
                                </div>
                            @endif
                        @endif

                        @if ($hasPulse)
                            @if (data_get($pulse, 'activation_phase_duration') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Activation Phase Duration</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'activation_phase_duration'), 's', 2) }}</dd>
                                </div>
                            @endif

                            @if (data_get($pulse, 'disperse_charge_time') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Disperse Charge Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'disperse_charge_time'), 's', 2) }}</dd>
                                </div>
                            @endif

                            @if (data_get($pulse, 'decrease_charge_rate_time') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Decrease Charge Rate Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'decrease_charge_rate_time'), 's', 2) }}</dd>
                                </div>
                            @endif

                            @if (data_get($pulse, 'increase_charge_rate_time') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Increase Charge Rate Time</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'increase_charge_rate_time'), 's', 2) }}</dd>
                                </div>
                            @endif
                        @endif

                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Jamming Max Power Draw</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($jamming, 'max_power_draw'), '', 2) }}</dd>
                            </div>

                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pulse Max Power Draw</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'max_power_draw'), '', 2) }}</dd>
                            </div>
                    </dl>
            </details>
        @endif

        {{-- Tertiary Data (Collapsible, closed by default) --}}
        @if ($hasPulse)
            @php
                $hasTertiaryData =
                    data_get($pulse, 'stop_charging_power_fraction') !== null ||
                    data_get($pulse, 'max_charge_rate_power_fraction') !== null ||
                    data_get($pulse, 'active_power_fraction') !== null ||
                    data_get($pulse, 'tethering_power_fraction') !== null ||
                    data_get($pulse, 'green_zone_check_range') !== null;
            @endphp

            @if ($hasTertiaryData)
                <details class="group">
                    <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                        <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                        Advanced Power & Range Details
                    </summary>
                        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                            @if (data_get($pulse, 'stop_charging_power_fraction') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Stop Charging Power Fraction</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'stop_charging_power_fraction'), '', 2) }}</dd>
                                </div>
                            @endif

                            @if (data_get($pulse, 'max_charge_rate_power_fraction') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Charge Rate Power Fraction</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'max_charge_rate_power_fraction'), '', 2) }}</dd>
                                </div>
                            @endif

                            @if (data_get($pulse, 'active_power_fraction') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Active Power Fraction</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'active_power_fraction'), '', 2) }}</dd>
                                </div>
                            @endif

                            @if (data_get($pulse, 'tethering_power_fraction') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Tethering Power Fraction</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'tethering_power_fraction'), '', 2) }}</dd>
                                </div>
                            @endif

                            @if (data_get($pulse, 'green_zone_check_range') !== null)
                                <div class="space-y-1">
                                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pulse Green Zone Check Range</dt>
                                    <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($pulse, 'green_zone_check_range'), 'm', 0) }}</dd>
                                </div>
                            @endif
                        </dl>
                </details>
            @endif
        @endif
    </div>
</div>
