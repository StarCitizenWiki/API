@props(['shield' => null])

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="shield" class="size-4 text-primary" />
            <span>Shield</span>
        </h2>

        <!-- Primary Section: Always Visible -->
        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Health</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($shield, 'max_health'), 'HP', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Rate</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($shield, 'regen_rate'), 'HP/s', 0) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Time</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($shield, 'regen_time'), 's', 2) }}</dd>
            </div>
        </dl>

        <!-- Secondary Section: Reserve Pool (collapsible, default open) -->
        @php
            $reservePool = data_get($shield, 'reserve_pool', []);
            $hasReservePool = collect($reservePool)->filter(fn($v) => $v !== null)->isNotEmpty();
        @endphp

        @if ($hasReservePool)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Reserve Pool
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                        @if (data_get($reservePool, 'regen_rate'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Rate</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($reservePool, 'regen_rate'), 'HP/s', 0) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'regen_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Time</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($reservePool, 'regen_time'), 's', 1) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'initial_health_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Initial Health Ratio</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($reservePool, 'initial_health_ratio'), '', 1) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'max_health_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Health Ratio</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($reservePool, 'max_health_ratio'), '', 1) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'regen_rate_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Rate Ratio</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($reservePool, 'regen_rate_ratio'), '', 1) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'drain_rate_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Drain Rate Ratio</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($reservePool, 'drain_rate_ratio'), '', 1) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        <!-- Tertiary Section: Regen Delay (collapsible, default closed) -->
        @php
            $regenDelay = data_get($shield, 'regen_delay', []);
            $hasRegenDelay = collect($regenDelay)->filter(fn($v) => $v !== null)->isNotEmpty();
        @endphp

        @if ($hasRegenDelay)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Regen Delay
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">
                        @if (data_get($regenDelay, 'downed'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Downed</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($regenDelay, 'downed'), 's', 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($regenDelay, 'damage'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($regenDelay, 'damage'), 's', 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif

        <!-- Tertiary Section: Absorption (collapsible, default closed) -->
        @php
            $absorption = data_get($shield, 'absorption');
            $hasAbsorption = is_array($absorption) && $absorption !== [];
        @endphp

        @if ($hasAbsorption)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Absorption
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-3">
                    @foreach ($absorption as $type => $values)
                        @php
                            $hasValues = is_array($values) && collect($values)->filter(fn($v) => $v !== null)->isNotEmpty();
                        @endphp
                        @if ($hasValues)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60"> {{ \Illuminate\Support\Str::headline($type) }}</dt>
                                <dd class="text-sm font-medium">
                                    {{ fmt(data_get($values, 'max'), 2) }}
                                </dd>
                            </div>
                        @endif
                    @endforeach
                    </dl>
                </div>
            </details>
        @endif

        <!-- Tertiary Section: Resistance (collapsible, default closed) -->
        @php
            $resistance = data_get($shield, 'resistance');
            $hasResistance = is_array($resistance) && $resistance !== [];
        @endphp

        @if ($hasResistance)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100">
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Resistance
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-3 grid-cols-2 sm:grid-cols-2">
                    @foreach ($resistance as $type => $values)
                        @php
                            $hasValues = is_array($values) && collect($values)->filter(fn($v) => $v !== null)->isNotEmpty();
                        @endphp
                        @if ($hasValues)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ \Illuminate\Support\Str::headline($type) }}</dt>
                                <dd class="text-sm font-medium">
                                    {{ fmt_range(data_get($values, 'min'), data_get($values, 'max'), '', 2) }}
                                </dd>
                            </div>
                        @endif
                    @endforeach
                    </dl>
                </div>
            </details>
        @endif
    </div>
</div>
