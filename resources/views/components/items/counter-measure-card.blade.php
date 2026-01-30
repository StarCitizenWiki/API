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
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="shield-user" class="size-4 text-primary" />
            <span>Counter Measure</span>
        </h2>

        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @if ($type !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type</dt>
                    <dd class="text-sm font-medium">{{ $type }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasSignatureData)
            <details class="collapse collapse-arrow border border-base-300 bg-base-100" open>
                <summary class="collapse-title min-h-11 py-3 text-sm font-semibold">
                    Signature
                </summary>
                <div class="collapse-content">
                    <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                        @if ($sigInfrared !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Infrared</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($sigInfrared, '', 2, true) }}</dd>
                            </div>
                        @endif
                        @if ($sigCrossSection !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($sigCrossSection, '', 2, true) }}</dd>
                            </div>
                        @endif
                        @if ($sigElectromagnetic !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ fmt_value_with_unit($sigElectromagnetic, '', 2, true) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </details>
        @endif
    </div>
</div>
