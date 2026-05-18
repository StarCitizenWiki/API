@php use Illuminate\Support\Str; @endphp
@props([
    'type',
    'subType' => null,
    'sizeMin' => null,
    'sizeMax' => null,
    'requiredTags' => null,
    'portTags' => null,
    'vehiclePortTags' => null,
    'browseUrl',
])

<div
    x-data="portEquippable(@js(['type' => $type, 'subType' => $subType, 'sizeMin' => $sizeMin, 'sizeMax' => $sizeMax, 'requiredTags' => $requiredTags, 'portTags' => $portTags, 'vehiclePortTags' => $vehiclePortTags]))"
>
    <button
        type="button"
        class="badge badge-sm badge-soft cursor-pointer hover:badge-primary transition-colors inline-flex items-center gap-1"
        @click="loadItems($event)"
        title="Browse equippable {{ $type }} items"
    >
        <x-icon name="list" class="size-3"/>
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-transition
            @click.away="close()"
            @keydown.escape.window="open && close()"
            :style="popupStyle || 'display:none'"
            class="rounded-box border border-base-300 bg-base-100 shadow-xl"
        >
            <div class="flex items-center justify-between px-3 py-2 border-b border-base-300">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">
                    Equippable {{ Str::headline($type) }}
                </span>
                <a href="{{ $browseUrl }}" class="link link-primary text-xs" title="View all on items page">
                    View all
                    <x-icon name="external-link" class="size-3 inline"/>
                </a>
            </div>

            <div
                class="flex items-center gap-2 px-3 py-1.5 bg-warning/10 border-b border-warning/20 text-xs text-warning">
                <x-icon name="info" class="size-3 shrink-0"/>
                <span>Results are a work in progress, filtering and stats may be incomplete.</span>
            </div>

            <div x-show="loading" class="flex items-center justify-center gap-2 px-3 py-6 text-sm text-muted">
                <span class="loading loading-spinner loading-sm"></span>
                <span>Loading&hellip;</span>
            </div>

            <div x-show="!loading && items.length > 0" class="max-h-80 overflow-y-auto overscroll-contain">
                <table class="table table-sm">
                    <thead class="sticky top-0 bg-base-100 z-10">
                    <tr>
                        <th class="text-xs font-semibold uppercase tracking-wider">Item</th>
                        <th class="text-xs font-semibold uppercase tracking-wider w-10 text-center">Size</th>
                        <th class="text-xs font-semibold uppercase tracking-wider">Info</th>
                        <th class="text-xs font-semibold uppercase tracking-wider">Stat</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template x-for="item in items" :key="item.web_url">
                        <tr class="hover">
                            <td class="max-w-40 truncate">
                                <a :href="item.web_url"
                                   class="link link-primary text-sm truncate inline-flex items-center gap-1">
                                    <span x-text="item.name"></span>
                                    <i data-lucide="external-link" class="size-3 shrink-0 opacity-50"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                    <span
                                        x-show="item.size !== null"
                                        class="badge badge-xs badge-ghost"
                                        x-text="'S' + item.size"
                                    ></span>
                            </td>
                            <td>
                                    <span
                                        x-show="item.annotation"
                                        class="text-xs text-subtle"
                                        x-text="item.annotation"
                                    ></span>
                            </td>
                            <td>
                                    <span
                                        x-show="item.stat"
                                        class="text-xs font-medium tabular-nums"
                                        x-text="item.stat"
                                    ></span>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>

            <div x-show="!loading && items.length === 0" class="flex flex-col items-center gap-2 px-4 py-6 text-center">
                <x-icon name="search-x" class="size-5 text-muted"/>
                <span class="text-sm text-muted">No matching items found</span>
            </div>
        </div>
    </template>
</div>
