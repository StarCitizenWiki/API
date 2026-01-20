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

    $hasSignature = is_array($signature) && array_filter($signature, fn($v) => $v !== null);
@endphp

<div class="card border border-base-200 bg-base-100 shadow-sm">
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="activity" class="size-4 text-primary" />
            <span>Counter Measure Specifications</span>
        </h2>

        <dl class="grid gap-4 sm:grid-cols-2">
            @if ($type !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type</dt>
                    <dd class="text-sm font-medium">{{ $type }}</dd>
                </div>
            @endif
        </dl>

        @if ($hasSignature)
            <div class="collapse collapse-arrow border border-base-200 bg-base-100">
                <input type="checkbox" />
                <div class="collapse-title text-sm font-semibold">Signature</div>
                <div class="collapse-content">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        @if ($sigInfrared !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Infrared</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sigInfrared, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sigCrossSection !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Cross Section</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sigCrossSection, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sigElectromagnetic !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Electromagnetic</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sigElectromagnetic, 2) }}</dd>
                            </div>
                        @endif
                        @if ($sigDecibel !== null)
                            <div class="space-y-1">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Decibel</dt>
                                <dd class="text-sm font-medium">{{ number_format((float)$sigDecibel, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        @endif
    </div>
</div>
