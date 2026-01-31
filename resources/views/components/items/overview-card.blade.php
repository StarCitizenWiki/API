@props(['item'])

@php
    $manufacturerName = data_get($item, 'manufacturer.name');
    $manufacturerCode = data_get($item, 'manufacturer.code');
    $type = data_get($item, 'type');
    $subType = data_get($item, 'sub_type');
    $size = data_get($item, 'size');
    $mass = data_get($item, 'mass');

    $dimension = data_get($item, 'dimension', []);
    $length = data_get($dimension, 'length');
    $width = data_get($dimension, 'width');
    $height = data_get($dimension, 'height');
    $volume = data_get($dimension, 'volume_converted', data_get($dimension, 'volume'));
    $volumeUnit = data_get($dimension, 'volume_converted_unit');
@endphp

<div {{ $attributes->merge(['class' => 'card border border-base-300 bg-base-100 shadow']) }}>
    <div class="card-body gap-3">
        <h2 class="card-title text-base">Overview</h2>
        <dl class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 tabular-nums">
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Manufacturer</dt>
                <dd class="text-sm font-medium">
                    @if ($manufacturerName)
                        <a href="{{ route('web.items.index', ['filter' => ['manufacturer' => $manufacturerName]]) }}" class="link link-primary">
                            {{ $manufacturerName }}
                        </a>
                        @if ($manufacturerCode)
                            <span class="badge badge-outline badge-sm ml-2">{{ $manufacturerCode }}</span>
                        @endif
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Type / Subtype</dt>
                <dd class="text-sm font-medium">
                    {{ $type ?? '-' }}@if ($subType) / {{ $subType }}@endif
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Size</dt>
                <dd class="text-sm font-medium">{{ $size ?? '-' }}</dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Mass</dt>
                <dd class="text-sm font-medium">
                    @if ($mass !== null)
                        {{ $mass }}kg
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Dimensions</dt>
                <dd class="text-sm font-medium">
                    @if ($length || $width || $height)
                        {{ $length ?? '-' }} x {{ $width ?? '-' }} x {{ $height ?? '-' }} m
                    @else
                        -
                    @endif
                </dd>
            </div>
            <div class="space-y-1">
                <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Volume</dt>
                <dd class="text-sm font-medium">
                    @if ($volume !== null)
                        {{ $volume }}@if ($volumeUnit) {{ $volumeUnit }}@endif
                    @else
                        -
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
