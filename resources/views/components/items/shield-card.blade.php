@props(['shield' => null])

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="shield" class="size-4 text-primary" />
            <span>Shield Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if (data_get($shield,'max_health'))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max Health</dt>
                    <dd class="text-sm font-medium">{{ (int)data_get($shield, 'max_health') }}</dd>
                </div>
            @endif

            @if (data_get($shield,'regen_rate'))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Rate</dt>
                    <dd class="text-sm font-medium">{{ (int)data_get($shield, 'regen_rate') }} / second</dd>
                </div>
            @endif

            @if (data_get($shield,'regen_time'))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen Time</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)data_get($shield, 'regen_time'), 2) }} s</dd>
                </div>
            @endif

            @if (data_get($shield,'decay_ratio'))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decay Ratio</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)data_get($shield, 'decay_ratio'), 2) }}</dd>
                </div>
            @endif

            @if (data_get($shield,'electrical_charge_damage_resistance'))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Electrical Charge
                        Damage Resistance
                    </dt>
                    <dd class="text-sm font-medium">{{ number_format((float)data_get($shield, 'electrical_charge_damage_resistance'), 2) }}</dd>
                </div>
            @endif
        </dl>

        @php
            $reservePool = data_get($shield, 'reserve_pool', []);
            $hasReservePool = is_array($reservePool) && array_filter($reservePool, fn($v) => $v !== null);
        @endphp

        @if ($hasReservePool)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Reserve Pool</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($reservePool, 'regen_rate'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen
                                    Rate
                                </dt>
                                <dd class="text-sm font-medium">{{ (int)data_get($reservePool, 'regen_rate') }} / second</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'regen_time'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen
                                    Time
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($reservePool, 'regen_time'), 2) }}
                                    s
                                </dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'initial_health_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Initial
                                    Health Ratio
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($reservePool, 'initial_health_ratio'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'max_health_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Max
                                    Health Ratio
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($reservePool, 'max_health_ratio'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'regen_rate_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Regen
                                    Rate Ratio
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($reservePool, 'regen_rate_ratio'), 2) }}</dd>
                            </div>
                        @endif

                        @if (data_get($reservePool, 'drain_rate_ratio'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Drain
                                    Rate Ratio
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($reservePool, 'drain_rate_ratio'), 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @php
            $regenDelay = data_get($shield, 'regen_delay', []);
            $hasRegenDelay = is_array($regenDelay) && array_filter($regenDelay, fn($v) => $v !== null);
        @endphp

        @if ($hasRegenDelay)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Regen Delay</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if (data_get($regenDelay, 'downed'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Downed
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($regenDelay, 'downed'), 2) }}
                                    s
                                </dd>
                            </div>
                        @endif

                        @if (data_get($regenDelay, 'damage'))
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Damage
                                </dt>
                                <dd class="text-sm font-medium">{{ number_format((float)data_get($regenDelay, 'damage'), 2) }}
                                    s
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif

        @php
            $absorption = data_get($shield, 'absorption');
            $hasAbsorption = is_array($absorption) && $absorption !== [];
        @endphp

        @if ($hasAbsorption)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Absorption</div>
                <div class="collapse-content">
                    @foreach ($absorption as $type => $values)
                        @php
                            $hasValues = is_array($values) && array_filter($values, fn($v) => $v !== null);
                        @endphp
                        @if ($hasValues)
                            <div class="mb-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">
                                    {{ \Illuminate\Support\Str::headline($type) }}
                                </div>
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @if (data_get($values, 'min'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Min
                                            </dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($values, 'min'), 2) }}</dd>
                                        </div>
                                    @endif

                                    @if (data_get($values, 'max'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Max
                                            </dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($values, 'max'), 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        @php
            $resistance = data_get($shield, 'resistance');
            $hasResistance = is_array($resistance) && $resistance !== [];
        @endphp

        @if ($hasResistance)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox"/>
                <div class="collapse-title text-sm font-semibold">Resistance</div>
                <div class="collapse-content">
                    @foreach ($resistance as $type => $values)
                        @php
                            $hasValues = is_array($values) && array_filter($values, fn($v) => $v !== null);
                        @endphp
                        @if ($hasValues)
                            <div class="mb-4">
                                <div class="text-xs font-semibold uppercase tracking-wide text-base-content/60 mb-2">
                                    {{ \Illuminate\Support\Str::headline($type) }}
                                </div>
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    @if (data_get($values, 'min'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Min
                                            </dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($values, 'min'), 2) }}</dd>
                                        </div>
                                    @endif

                                    @if (data_get($values, 'max'))
                                        <div class="space-y-1">
                                            <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                                                Max
                                            </dt>
                                            <dd class="text-sm font-medium">{{ number_format((float)data_get($values, 'max'), 2) }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
