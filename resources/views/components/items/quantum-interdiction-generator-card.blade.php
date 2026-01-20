@props(['quantumInterdictionGenerator' => null])

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="mouse-pointer-2-off" class="size-4 text-primary" />
            <span>Quantum Interdiction Generator Specifications</span>
        </h2>

        @php
            $powerFractions = data_get($quantumInterdictionGenerator, 'power_fractions', []);
            $hasPowerFractions = is_array($powerFractions) && array_filter($powerFractions, static fn($v) => $v !== null);
        @endphp

        @if ($hasPowerFractions)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Power Fractions</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($powerFractions, 'base'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Base</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($powerFractions, 'base'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($powerFractions, 'pulse'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Pulse</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($powerFractions, 'pulse'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($powerFractions, 'jammer'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Jammer</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($powerFractions, 'jammer'), 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @php
            $jamming = data_get($quantumInterdictionGenerator, 'jamming', []);
            $hasJamming = is_array($jamming) && array_filter($jamming, fn($v) => $v !== null);
        @endphp

        @if ($hasJamming)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Jamming</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($jamming, 'range') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Range</dt>
                                <dd class="text-sm font-medium">{{ number_format((int)data_get($jamming, 'range')) }} m</dd>
                            </div>
                        @endif

                        @if (data_get($jamming, 'max_power_draw'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Power Draw</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($jamming, 'max_power_draw'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($jamming, 'green_zone_check_range'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Green Zone Check Range</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($jamming, 'green_zone_check_range') }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @php
            $pulse = data_get($quantumInterdictionGenerator, 'pulse', []);
            $hasPulse = is_array($pulse) && array_filter($pulse, fn($v) => $v !== null);
        @endphp

        @if ($hasPulse)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Pulse</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($pulse, 'charge_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Charge Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'charge_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'discharge_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Discharge Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'discharge_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'cooldown_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'cooldown_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'radius'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radius</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($pulse, 'radius') }} m</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'decrease_charge_rate_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decrease Charge Rate Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'decrease_charge_rate_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'increase_charge_rate_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Increase Charge Rate Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'increase_charge_rate_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'activation_phase_duration'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Activation Phase Duration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'activation_phase_duration'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'disperse_charge_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Disperse Charge Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'disperse_charge_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'max_power_draw'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Power Draw</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'max_power_draw'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'stop_charging_power_fraction'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stop Charging Power Fraction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'stop_charging_power_fraction'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'max_charge_rate_power_fraction'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Charge Rate Power Fraction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'max_charge_rate_power_fraction'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'active_power_fraction'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Active Power Fraction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'active_power_fraction'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'tethering_power_fraction'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tethering Power Fraction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'tethering_power_fraction'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'green_zone_check_range'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Green Zone Check Range</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($pulse, 'green_zone_check_range') }} m</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'discharge_time') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Discharge Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'discharge_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'cooldown_time') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cooldown Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'cooldown_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'radius') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Radius</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($pulse, 'radius') }} m</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'decrease_charge_rate_time') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decrease Charge Rate Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'decrease_charge_rate_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'increase_charge_rate_time') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Increase Charge Rate Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'increase_charge_rate_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'activation_phase_duration') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Activation Phase Duration</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'activation_phase_duration'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'disperse_charge_time') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Disperse Charge Time</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'disperse_charge_time'), 2) }} s</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'max_power_draw') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Power Draw</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'max_power_draw'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'stop_charging_power_fraction') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Stop Charging Power Fraction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'stop_charging_power_fraction'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'max_charge_rate_power_fraction') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Charge Rate Power Fraction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'max_charge_rate_power_fraction'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'active_power_fraction') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Active Power Fraction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'active_power_fraction'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'tethering_power_fraction') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Tethering Power Fraction</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($pulse, 'tethering_power_fraction'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($pulse, 'green_zone_check_range') !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Green Zone Check Range</dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($pulse, 'green_zone_check_range') }} m</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
