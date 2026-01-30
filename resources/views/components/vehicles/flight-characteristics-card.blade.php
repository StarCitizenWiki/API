@props(['vehicle'])

@php
    $speed = data_get($vehicle, 'speed', []);
    $agility = data_get($vehicle, 'agility', []);
    $afterburner = data_get($vehicle, 'afterburner', []);
    $crew = data_get($vehicle, 'crew', []);

    $acceleration = data_get($agility, 'acceleration');
    $hasAcceleration = is_array($acceleration) && $acceleration !== [];
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-2">
        <h2 class="card-title">Flight Characteristics</h2>

        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">Speed</h3>
            <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                <div class="space-y-1">
                    <dt class="text-xs text-base-content/60">SCM</dt>
                    <dd class="text-sm">{{ fmt_value_with_unit(data_get($speed, 'scm'), 'm/s', 0) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs text-base-content/60">Max</dt>
                    <dd class="text-sm">{{ fmt_value_with_unit(data_get($speed, 'max'), 'm/s', 0) }}</dd>
                </div>
                @if (data_get($speed, 'boost_forward'))
                <div class="space-y-1">
                    <dt class="text-xs text-base-content/60">Boost Fwd</dt>
                    <dd class="text-sm">{{ fmt_value_with_unit(data_get($speed, 'boost_forward'), 'm/s', 0) }}</dd>
                </div>
                @endif
                @if (data_get($speed, 'boost_backward'))
                <div class="space-y-1">
                    <dt class="text-xs text-base-content/60">Boost Back</dt>
                    <dd class="text-sm">{{ fmt_value_with_unit(data_get($speed, 'boost_backward'), 'm/s', 0) }}</dd>
                </div>
                @endif
                <div class="space-y-1">
                    <dt class="text-xs text-base-content/60">Boost Regen.</dt>
                    <dd class="text-sm">{{ fmt_value_with_unit(data_get($afterburner, 'regen_time'), 's', 1) }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-xs text-base-content/60">Regen. Delay</dt>
                    <dd class="text-sm">{{ fmt_value_with_unit(data_get($afterburner, 'regen_delay'), 's', 1) }}</dd>
                </div>
            </dl>
        </div>

        @if (data_get($agility, 'pitch') || data_get($agility, 'yaw') || data_get($agility, 'roll'))
        <div class="mt-4">
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">Agility</h3>
            <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 md:grid-cols-3">
                @if (data_get($agility, 'pitch'))
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Pitch</dt>
                        <dd class="text-sm">{{ number_format(data_get($agility, 'pitch'), 1) }} °/s</dd>
                    </div>
                @endif
                @if (data_get($agility, 'yaw'))
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Yaw</dt>
                        <dd class="text-sm">{{ number_format(data_get($agility, 'yaw'), 1) }} °/s</dd>
                    </div>
                @endif
                @if (data_get($agility, 'roll'))
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Roll</dt>
                        <dd class="text-sm">{{ number_format(data_get($agility, 'roll'), 1) }} °/s</dd>
                    </div>
                @endif
                @if (data_get($agility, 'pitch_boosted'))
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Pitch Boost</dt>
                        <dd class="text-sm">{{ number_format(data_get($agility, 'pitch_boosted'), 1) }} °/s</dd>
                    </div>
                @endif
                @if (data_get($agility, 'yaw_boosted'))
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Yaw Boost</dt>
                        <dd class="text-sm">{{ number_format(data_get($agility, 'yaw_boosted'), 1) }} °/s</dd>
                    </div>
                @endif
                @if (data_get($agility, 'roll_boosted'))
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Roll Boost</dt>
                        <dd class="text-sm">{{ number_format(data_get($agility, 'roll_boosted'), 1) }} °/s</dd>
                    </div>
                @endif
            </dl>
        </div>
        @endif
    </div>
</div>
