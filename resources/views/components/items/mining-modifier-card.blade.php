@props(['miningModifier' => null])

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="gem" class="size-4 text-primary" />
            <span>Mining Modifier</span>
        </h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type</dt>
                <dd class="text-sm font-medium">{{ data_get($miningModifier, 'item_type') }} ({{ data_get($miningModifier, 'type') }})</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Charges</dt>
                <dd class="text-sm font-medium">
                    @if (data_get($miningModifier, 'charges') !== null)
                        {{ fmt((int)data_get($miningModifier, 'charges'), 0) }}
                    @else
                        Unlimited
                    @endif
                </dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Duration</dt>
                <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($miningModifier, 'duration'), 's', 2) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Power Modifier</dt>
                <dd class="text-sm font-medium">
                    @if (is_numeric(data_get($miningModifier, 'power_modifier')))
                        {{ fmt_value_with_unit((float)data_get($miningModifier, 'power_modifier'), 'x', 2) }}
                    @else
                        {{ fmt_or_dash(data_get($miningModifier, 'power_modifier')) }}
                    @endif
                </dd>
            </div>
        </dl>

        @php
            $modifierMap = data_get($miningModifier, 'modifier_map', []);
            $hasModifiers = is_array($modifierMap) && $modifierMap !== [];
        @endphp

        @if ($hasModifiers)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">Modifiers</summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($modifierMap as $key => $value)
                            @php
                                $displayKey = \Illuminate\Support\Str::headline($key);
                                $displayValue = is_numeric($value) ? fmt((float)$value, 0) : fmt_or_dash($value);
                            @endphp
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ $displayKey }}</dt>
                                <dd class="text-sm font-medium">{{ $displayValue }}%</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </details>
        @endif
    </div>
</div>
