@props(['miningModifier' => null])

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="cog" class="size-4 text-primary" />
            <span>Mining Modifier Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if (data_get($miningModifier, 'type') !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type</dt>
                    <dd class="text-sm font-medium">{{ data_get($miningModifier, 'type') }}</dd>
                </div>
            @endif

            @if (data_get($miningModifier, 'item_type') !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Item Type</dt>
                    <dd class="text-sm font-medium">{{ data_get($miningModifier, 'item_type') }}</dd>
                </div>
            @endif

            @if (array_key_exists('charges', $miningModifier ?? []))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Charges</dt>
                    <dd class="text-sm font-medium">{{ data_get($miningModifier, 'charges') !== null ? (int)data_get($miningModifier, 'charges') : 'Unlimited' }}</dd>
                </div>
            @endif

            @if (data_get($miningModifier, 'duration') !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Duration</dt>
                    <dd class="text-sm font-medium">{{ number_format((float)data_get($miningModifier, 'duration'), 2) }} s</dd>
                </div>
            @endif

            @if (data_get($miningModifier, 'power_modifier') !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Power Modifier</dt>
                    <dd class="text-sm font-medium">
                        @if (is_numeric(data_get($miningModifier, 'power_modifier')))
                            {{ number_format((float)data_get($miningModifier, 'power_modifier'), 2) }}x
                        @else
                            {{ data_get($miningModifier, 'power_modifier') }}
                        @endif
                    </dd>
                </div>
            @endif
        </dl>

        @php
            $modifierMap = data_get($miningModifier, 'modifier_map', []);
            $hasModifiers = is_array($modifierMap) && $modifierMap !== [];
        @endphp

        @if ($hasModifiers)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Modifiers</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @foreach ($modifierMap as $key => $value)
                            @php
                                $displayKey = \Illuminate\Support\Str::headline($key);
                                $displayValue = is_numeric($value) ? number_format((float)$value) : $value;
                            @endphp
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ $displayKey }}</dt>
                                <dd class="text-sm font-medium">{{ $displayValue }}%</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
