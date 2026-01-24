@props(['vehicle'])

@php
    $fuel = data_get($vehicle, 'fuel', []);
    $quantum = data_get($vehicle, 'quantum', []);
@endphp

<details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
    <summary class="collapse-title min-h-11 py-3 font-semibold">
        Fuel & Quantum
    </summary>
    <div class="collapse-content">
        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">Fuel</h3>
            <dl class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4">
                @if (data_get($fuel, 'capacity'))
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Capacity</dt>
                        <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($fuel, 'capacity'), 'SCU', 0) }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Quantum -->
        @if (data_get($quantum, 'quantum_speed') || data_get($quantum, 'quantum_spool_time') || data_get($quantum, 'quantum_fuel_capacity'))
            <div class="mt-4">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">Quantum</h3>
                <dl class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4">
                    @if (data_get($quantum, 'quantum_speed'))
                        <div class="space-y-1">
                            <dt class="text-xs text-base-content/60">Speed</dt>
                            <dd class="text-sm font-medium">{{ fmt_compact(data_get($quantum, 'quantum_speed'), 0) }} m/s</dd>
                        </div>
                    @endif
                    @if (data_get($quantum, 'quantum_spool_time'))
                        <div class="space-y-1">
                            <dt class="text-xs text-base-content/60">Spool Time</dt>
                            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($quantum, 'quantum_spool_time'), 's', 2) }}</dd>
                        </div>
                    @endif
                    @if (data_get($quantum, 'quantum_fuel_capacity'))
                        <div class="space-y-1">
                            <dt class="text-xs text-base-content/60">Fuel Capacity</dt>
                            <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($quantum, 'quantum_fuel_capacity'), 'SCU', 2) }}</dd>
                        </div>
                    @endif
                    @if (data_get($quantum, 'quantum_range'))
                        <div class="space-y-1">
                            <dt class="text-xs text-base-content/60">Range</dt>
                            <dd class="text-sm font-medium">{{ fmt_compact(data_get($quantum, 'quantum_range'), 2) }} m</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif
    </div>
</details>
