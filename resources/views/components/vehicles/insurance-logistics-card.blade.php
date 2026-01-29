@props(['vehicle'])

@php
    $insurance = data_get($vehicle, 'insurance', []);
@endphp

<details class="collapse collapse-arrow border border-base-300 bg-base-100 shadow">
    <summary class="collapse-title min-h-11 py-3 font-semibold">
        Insurance
    </summary>
    <div class="collapse-content">
    @if ($insurance !== [])
        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @if (data_get($insurance, 'claim_time'))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Claim Time</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($insurance, 'claim_time'), 'min', 1) }}</dd>
                </div>
            @endif
            @if (data_get($insurance, 'expedite_time'))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Expedite Time</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($insurance, 'expedite_time'), 'min', 1) }}</dd>
                </div>
            @endif
            @if (data_get($insurance, 'expedite_cost'))
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Expedite Cost</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit(data_get($insurance, 'expedite_cost'), 'aUEC', 0) }}</dd>
                </div>
            @endif
        </dl>
    @else
        <p class="text-sm text-base-content/60">No insurance data available</p>
    @endif
    </div>
</details>
