@php use Illuminate\Support\Str; @endphp
@props([
    'port',
])

@php
    $portIdentifier = data_get($port, 'name');
    $portLabel = Str::of($portIdentifier ?? 'Port')->headline();
    $portHash = substr(md5(json_encode($port)), 0, 8);
    $portSlug = Str::slug($portIdentifier ?? 'port');
    $portId = ($portSlug !== '' ? $portSlug : 'port').'-'.$portHash;

    // Extract equipped item stats for summary display
    $equippedItem = data_get($port, 'equipped_item', data_get($port, 'equipped_port_item'));
    $showQuickStats = !empty($equippedItem);

    if ($showQuickStats) {
        $itemSize = data_get($equippedItem, 'size');
    }

    $sizeMin = data_get($port, 'sizes.min');
    $sizeMax = data_get($port, 'sizes.max');
    $sizeRange = fmt_range($sizeMin, $sizeMax, '');

    $portTypes = data_get($port, 'compatible_types', []) ?? [];
    $portTypeCount = is_array($portTypes) ? count($portTypes) : 0;

@endphp

<div class="port-entry">
    <details
        id="{{ $portId }}"
        class="collapse collapse-arrow border border-base-300 bg-base-100 shadow"
    >
        <summary
            class="collapse-title min-h-11 py-3 text-sm font-semibold flex items-center justify-between gap-2 flex-wrap"
            aria-controls="{{ $portId }}-content"
        >
            <span class="flex items-center gap-2">
                @if(data_get($port, 'uneditable'))
                    <x-icon name="lock" class="size-3"/>
                @endif
                {{ $portLabel ?? 'Port' }}
            </span>

            <span class="flex items-center gap-2 text-xs font-normal flex-wrap">
                @if ($sizeRange !== '-')
                    <span class="badge badge-ghost badge-sm">S{{ $sizeRange }}</span>
                @endif
                @if ($portTypeCount > 0)
                    <span class="badge badge-ghost badge-sm">{{ $portTypeCount }} types</span>
                @endif
                @if ($showQuickStats && data_get($equippedItem, 'name'))
                    <span class="max-w-[12rem] truncate text-xs text-base-content/70">
                        Equipped: {{ data_get($equippedItem, 'name') }}
                    </span>
                @endif
                @if ($showQuickStats && $itemSize !== null)
                    <span class="badge badge-sm badge-soft" title="Item Size">S{{ $itemSize }}</span>
                @endif
            </span>
        </summary>
        <div id="{{ $portId }}-content" class="collapse-content">
            <dl class="grid gap-3 sm:grid-cols-2 tabular-nums">
                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Equippable Item Size
                    </dt>
                    <dd class="text-sm">
                        S{{ fmt_range(data_get($port, 'sizes.min'), data_get($port, 'sizes.max'), '') }}
                    </dd>
                </div>

                <div class="space-y-1">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-base-content/60">
                        Required Type + Sub Type
                    </dt>
                    <dd class="text-sm">
                        <div class="flex flex-col gap-1">
                            @foreach ($portTypes as $portType)
                                <div>
                                    {{ data_get($portType, 'type') ?? '-' }} / {{ implode(', ', data_get($portType, 'sub_types', [])) }}
                                </div>
                            @endforeach
                        </div>
                    </dd>
                </div>

                @unless(empty(data_get($port, 'equipped_item')))
                <div class="space-y-1 col-span-full">
                    <dt class="font-semibold text-sm uppercase tracking-wide">
                        Equipped Item
                    </dt>
                    <dd class="text-sm">
                        <div class="flex items-center gap-2">
                            <span>{{ data_get($port, 'equipped_item.name') ?? '-' }}</span>
                            @if (! empty(data_get($port, 'equipped_item.uuid')))
                                <a href="{{ route('web.items.show', data_get($port, 'equipped_item.uuid')) }}"
                                   class="link link-primary">View</a>
                            @endif
                        </div>
                    </dd>
                </div>
                @endunless
            </dl>

        </div>
    </details>
</div>
