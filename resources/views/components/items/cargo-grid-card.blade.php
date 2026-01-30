@props([
    'cargoGrid',
 ])

@php
    $scuConverted = data_get($cargoGrid, 'scu');
    $isOpen = data_get($cargoGrid, 'open');
    $isExternal = data_get($cargoGrid, 'external');
    $isClosed = data_get($cargoGrid, 'closed');
    $width = data_get($cargoGrid, 'width');
    $height = data_get($cargoGrid, 'height');
    $length = data_get($cargoGrid, 'length');

    $hasDimensions = $width !== null && $height !== null && $length !== null;
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow'])}}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base flex items-center gap-2">
            <x-icon name="box" class="size-4 text-primary" />
            <span>Cargo Grid</span>
        </h2>


        <dl class="grid gap-4 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @if ($scuConverted !== null)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Capacity</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($scuConverted, 'SCU', 1) }}</dd>
                </div>
            @endif
            @if ($hasDimensions)
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Dimensions</dt>
                    <dd class="text-sm font-medium">{{ fmt_value_with_unit($width, 'm', 1) }} × {{ fmt_value_with_unit($height, 'm', 1) }} × {{ fmt_value_with_unit($length, 'm', 1) }}</dd>
                </div>
            @endif
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type</dt>
                <dd class="text-sm font-medium">
                    {{ $isOpen ? 'Open' : ($isClosed ? 'Closed' : ($isExternal ? 'External' : '-')) }}
                </dd>
            </div>
        </dl>
    </div>
</div>
