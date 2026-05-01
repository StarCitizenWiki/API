@props(['shield' => null])

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Shield</h2>

        <!-- Primary Section: Always Visible -->
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Health</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($shield, 'max_health'), 'HP', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Regen Rate</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($shield, 'regen_rate'), 'HP/s', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Regen Time</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($shield, 'regen_time'), 's', 2) }}</dd>
            </div>
        </dl>

        <!-- Secondary Section: Reserve Pool (collapsible, default open) -->
        @php
            $reservePool = data_get($shield, 'reserve_pool', []);
            $hasReservePool = collect($reservePool)->filter(fn($v) => $v !== null)->isNotEmpty();
        @endphp

        @if ($hasReservePool)
            <details class="group" data-testid="shield-card-reserve-pool" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Reserve Pool
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($reservePool, 'regen_rate'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Regen Rate</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($reservePool, 'regen_rate'), 'HP/s', 0) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'regen_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Regen Time</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($reservePool, 'regen_time'), 's', 1) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'initial_health_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Initial Health Ratio</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($reservePool, 'initial_health_ratio'), '', 1) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'max_health_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Max Health Ratio</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($reservePool, 'max_health_ratio'), '', 1) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'regen_rate_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Regen Rate Ratio</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($reservePool, 'regen_rate_ratio'), '', 1) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'drain_rate_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Drain Rate Ratio</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($reservePool, 'drain_rate_ratio'), '', 1) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        <!-- Tertiary Section: Regen Delay (collapsible, default closed) -->
        @php
            $regenDelay = data_get($shield, 'regen_delay', []);
            $hasRegenDelay = collect($regenDelay)->filter(fn($v) => $v !== null)->isNotEmpty();
        @endphp

        @if ($hasRegenDelay)
            <details class="group" data-testid="shield-card-regen-delay">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Regen Delay
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if (data_get($regenDelay, 'downed'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Downed</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($regenDelay, 'downed'), 's', 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($regenDelay, 'damage'))
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Damage</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($regenDelay, 'damage'), 's', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif

        <!-- Tertiary Section: Absorption (collapsible, default closed) -->
        @php
            $absorption = data_get($shield, 'absorption');
            $hasAbsorption = is_array($absorption) && $absorption !== [];
        @endphp

        @if ($hasAbsorption)
            <details class="group" data-testid="shield-card-absorption">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Absorption
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                    @foreach ($absorption as $type => $values)
                        @php
                            $hasValues = is_array($values) && collect($values)->filter(fn($v) => $v !== null)->isNotEmpty();
                        @endphp
                        @if ($hasValues)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted"> {{ \Illuminate\Support\Str::headline($type) }}</dt>
                                <dd class="text-sm font-semibold text-base-content">
                                    {{ fmt(data_get($values, 'max'), 2) }}
                                </dd>
                            </div>
                        @endif
                    @endforeach
                    </dl>
            </details>
        @endif

        <!-- Tertiary Section: Resistance (collapsible, default closed) -->
        @php
            $resistance = data_get($shield, 'resistance');
            $hasResistance = is_array($resistance) && $resistance !== [];
        @endphp

        @if ($hasResistance)
            <details class="group" data-testid="shield-card-resistance">
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Resistance
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                    @foreach ($resistance as $type => $values)
                        @php
                            $hasValues = is_array($values) && collect($values)->filter(fn($v) => $v !== null)->isNotEmpty();
                        @endphp
                        @if ($hasValues)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">{{ \Illuminate\Support\Str::headline($type) }}</dt>
                                <dd class="text-sm font-semibold text-base-content">
                                    {{ fmt_range(data_get($values, 'min'), data_get($values, 'max'), '', 2) }}
                                </dd>
                            </div>
                        @endif
                    @endforeach
                    </dl>
            </details>
        @endif
    </div>
</div>
