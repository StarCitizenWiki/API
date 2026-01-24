@props(['vehicle'])

@php
    $shield = data_get($vehicle, 'shield', []);
    $health = data_get($vehicle, 'health');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-4">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="heroicon-o-shield-check" class="size-4 text-primary" />
            <span>Defense Systems</span>
        </h2>

        @if ($shield !== [])
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">Shield</h3>
                <dl class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4">
                    @if (data_get($shield, 'hp'))
                        <div class="space-y-1">
                            <dt class="text-xs text-base-content/60">HP</dt>
                            <dd class="text-sm font-medium">{{ number_format(data_get($shield, 'hp'), 0) }}</dd>
                        </div>
                    @endif
                    @if (data_get($shield, 'regen'))
                        <div class="space-y-1">
                            <dt class="text-xs text-base-content/60">Regen</dt>
                            <dd class="text-sm font-medium">{{ number_format(data_get($shield, 'regen'), 2) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        @if ($health)
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/60">Health</h3>
                <dl class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4">
                    <div class="space-y-1">
                        <dt class="text-xs text-base-content/60">Total</dt>
                        <dd class="text-sm font-medium">{{ number_format($health, 0) }}</dd>
                    </div>
                </dl>
            </div>
        @endif
    </div>
</div>
