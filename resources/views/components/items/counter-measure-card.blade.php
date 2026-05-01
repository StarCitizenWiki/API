@props([
    'counterMeasure',
])

@php
    $type = data_get($counterMeasure, 'type');

    $signature = data_get($counterMeasure, 'signature', []);
    $sigInfrared = data_get($signature, 'infrared');
    $sigCrossSection = data_get($signature, 'cross_section');
    $sigElectromagnetic = data_get($signature, 'electromagnetic');
    $sigDecibel = data_get($signature, 'decibel');

    $hasSignatureData = $sigInfrared !== null || $sigCrossSection !== null || $sigElectromagnetic !== null;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base">Counter Measure</h2>

        <dl class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            @if ($type !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-medium uppercase tracking-wide text-muted">Type</dt>
                    <dd class="text-sm font-semibold text-base-content">{{ $type }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasSignatureData)
            <details class="group" open>
                <summary class="flex cursor-pointer items-center gap-2 py-2 text-sm font-semibold text-subtle list-none [&::-webkit-details-marker]:hidden">
                    <x-icon name="chevron-right" class="size-3 shrink-0 transition-transform group-open:rotate-90" />
                    Signature
                </summary>
                    <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 pt-1 pb-2">
                        @if ($sigInfrared !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Infrared</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($sigInfrared, '', 2, true) }}</dd>
                            </div>
                        @endif
                        @if ($sigCrossSection !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Cross Section</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($sigCrossSection, '', 2, true) }}</dd>
                            </div>
                        @endif
                        @if ($sigElectromagnetic !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-medium uppercase tracking-wide text-muted">Electromagnetic</dt>
                                <dd class="text-sm font-semibold text-base-content">{{ fmt_value_with_unit($sigElectromagnetic, '', 2, true) }}</dd>
                            </div>
                        @endif
                    </dl>
            </details>
        @endif
    </div>
</div>
