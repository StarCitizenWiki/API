@php use Illuminate\Support\Str; @endphp
@props([
    'port',
])

@php
    $portIdentifier = data_get($port, 'name');

    // Extract equipped item stats for summary display
    $equippedItem = data_get($port, 'equipped_item', data_get($port, 'equipped_port_item'));
    $showQuickStats = !empty($equippedItem);

    if ($showQuickStats) {
        $itemSize = data_get($equippedItem, 'size');
    }

    $sizeMin = data_get($port, 'sizes.min');
    $sizeMax = data_get($port, 'sizes.max');

    $portTypes = data_get($port, 'compatible_types', []) ??[];

@endphp

<div class="port-entry">
    <details
        id="{{ $portIdentifier }}"
        class="collapse collapse-arrow border border-base-300 bg-base-100 shadow"
    >
        <summary
            class="collapse-title min-h-11 py-3 text-sm font-semibold flex items-center justsify-between gap-2 flex-wrap"
            aria-expanded="false"
            aria-controls="{{ $portIdentifier }}-content"
        >
            <span class="flex items-center gap-2">
                @if(data_get($port, 'uneditable'))
                    <x-icon name="lock" class="size-3"/>
                @endif
                {{ Str::of(data_get($port, 'name'))->headline() ?? 'Port' }}
            </span>

            @if ($showQuickStats)
                <span class="flex items-center gap-2 text-xs font-normal flex-wrap">
                    @if ($itemSize !== null)
                        <span class="badge badge-sm badge-soft" title="Item Size">S{{ $itemSize }}</span>
                    @endif
                </span>
            @endif
        </summary>
        <div id="{{ $portIdentifier }}-content" class="collapse-content">
            <dl class="grid gap-3 sm:grid-cols-2 md:grid-cols-2">
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
                        @foreach ($portTypes as $portType)
                            <div>
                                {{ data_get($portType, 'type') ?? '-' }}  / {{ implode(', ', data_get($portType, 'sub_types', [])) }}
                            </div>
                        @endforeach
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
