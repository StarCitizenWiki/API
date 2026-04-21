@props(['miningModifier' => null])

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Mining Modifier</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Type</dt>
                <dd class="text-sm font-semibold text-base-content">{{ data_get($miningModifier, 'item_type') }} ({{ data_get($miningModifier, 'type') }})</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Charges</dt>
                <dd class="text-sm font-semibold text-base-content">
                    @if (data_get($miningModifier, 'charges') !== null)
                        {{ fmt((int)data_get($miningModifier, 'charges'), 0) }}
                    @else
                        Unlimited
                    @endif
                </dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Duration</dt>
                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit(data_get($miningModifier, 'duration'), 's', 2) }}</dd>
            </div>

            <div class="space-y-1">
                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">Power Modifier</dt>
                <dd class="text-sm font-semibold text-base-content">
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
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-base-content/70 list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Modifiers
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @foreach ($modifierMap as $key => $value)
                            @php
                                $displayKey = \Illuminate\Support\Str::headline($key);
                                $displayValue = is_numeric($value) ? fmt((float)$value, 0) : fmt_or_dash($value);
                            @endphp
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-base-content/45">{{ $displayKey }}</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ $displayValue }}%</dd>
                            </div>
                        @endforeach
                    </dl>
            </details>
        @endif
    </div>
</div>
