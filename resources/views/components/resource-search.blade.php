@props([
    'title',
    'description' => null,
    'route',
    'placeholder',
    'variant' => 'prominent',
    'apiEndpoint',
    'helpText' => null,
])

@php($alpineId = 'ls_' . \Illuminate\Support\Str::random(8))

@if ($variant === 'minimal')
    <div data-remove {{ $attributes->merge(['class' => 'rounded-box border border-base-300 bg-base-200 p-3 sm:p-4']) }}>
        <form method="GET" action="{{ $route }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 space-y-1 lg:max-w-sm xl:max-w-md">
                <h2 class="text-sm font-semibold tracking-tight text-emphasis">{{ $title }}</h2>
                @if ($description)
                    <p class="text-xs leading-5 text-subtle">{{ $description }}</p>
                @endif
            </div>
            <div
                x-data="liveSearch('{{ $apiEndpoint }}')"
                x-init="init()"
                x-on:click.window="closeOnOutside($event)"
                class="join join-vertical w-full sm:join-horizontal lg:max-w-xl"
            >
                <label class="input input-bordered join-item flex w-full items-center gap-2 bg-base-100">
                    <x-icon name="search" class="size-4 text-muted" x-show="!loading" />
                    <span class="loading loading-spinner loading-xs text-muted" x-show="loading"></span>
                    <input
                        x-ref="input"
                        type="search"
                        name="filter[name]"
                        class="grow text-sm"
                        placeholder="{{ $placeholder }}"
                        x-model="query"
                        x-on:input="search()"
                        x-on:keydown="navigate($event)"
                        x-on:focus="results.length && query.trim().length >= 2 && (open = true)"
                        autocomplete="off"
                    />
                </label>
                <button class="btn btn-secondary join-item px-4 sm:shrink-0" type="submit">Search</button>

                {{-- Dropdown --}}
                <div
                    x-ref="dropdown"
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    x-init="updateDropdownPosition()"
                    role="listbox"
                    class="rounded-box border border-base-300 bg-base-100 shadow-xl max-h-96 overflow-y-auto overflow-x-hidden"
                    style="display: none;"
                >
                    {{-- Empty state --}}
                    <template x-if="results.length === 0">
                        <div class="flex flex-col items-center gap-2 px-4 py-6 text-center">
                            <x-icon name="search-x" class="size-6 text-muted" />
                            <span class="text-sm text-muted">No results found</span>
                        </div>
                    </template>

                    {{-- Results list --}}
                    <ul x-show="results.length > 0" class="menu menu-sm p-1 gap-0.5 w-full">
                        <template x-for="(item, idx) in results" :key="item.web_url ?? idx">
                            <li>
                                <a
                                    :href="item.web_url"
                                    role="option"
                                    data-live-search-item
                                    class="grid grid-cols-[auto_1fr] items-center gap-x-2 rounded-lg px-3 py-2 text-sm w-full"
                                    :class="{ 'bg-base-200': idx === activeIndex }"
                                    x-on:click.prevent="selectItem(idx)"
                                    x-on:mouseenter="activeIndex = idx"
                                >
                                    <span x-show="item.type_label" x-text="item.type_label" class="text-xs text-muted truncate max-w-20"></span>
                                    <span x-show="!item.type_label">&nbsp;</span>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <x-icon name="arrow-right" class="size-3.5 shrink-0 text-muted" />
                                        <span x-text="item.name ?? item.title" class="truncate"></span>
                                        <span
                                            x-show="itemLabel(item)"
                                            class="text-xs text-muted shrink-0 pl-1"
                                            x-text="itemLabel(item)"
                                        ></span>
                                    </div>
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </form>
    </div>
@else
    <div data-remove {{ $attributes->merge(['class' => 'card card-border bg-base-100 shadow']) }}>
        <form method="GET" action="{{ $route }}" class="card-body gap-4">
            <div class="flex flex-col gap-2">
                <h2 class="card-title text-base">{{ $title }}</h2>
                @if ($description)
                    <p class="text-sm text-subtle">{{ $description }}</p>
                @endif
            </div>
            <fieldset class="fieldset">
                <div
                    x-data="liveSearch('{{ $apiEndpoint }}')"
                    x-init="init()"
                    x-on:click.window="closeOnOutside($event)"
                    class="flex flex-col gap-3 sm:flex-row"
                >
                    <label class="input input-bordered flex w-full items-center gap-2">
                        <x-icon name="search" class="size-4 text-subtle" x-show="!loading" />
                        <span class="loading loading-spinner loading-xs text-subtle" x-show="loading"></span>
                        <input
                            x-ref="input"
                            type="search"
                            name="filter[name]"
                            class="w-full"
                            placeholder="{{ $placeholder }}"
                            x-model="query"
                            x-on:input="search()"
                            x-on:keydown="navigate($event)"
                            x-on:focus="results.length && query.trim().length >= 2 && (open = true)"
                            autocomplete="off"
                        />
                    </label>
                    <button class="btn btn-primary sm:shrink-0" type="submit">Search</button>

                    {{-- Dropdown --}}
                    <div
                        x-ref="dropdown"
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-init="updateDropdownPosition()"
                        role="listbox"
                        class="rounded-box border border-base-300 bg-base-100 shadow-xl max-h-96 overflow-y-auto overflow-x-hidden"
                        style="display: none;"
                    >
                        {{-- Empty state --}}
                        <template x-if="results.length === 0">
                            <div class="flex flex-col items-center gap-2 px-4 py-6 text-center">
                                <x-icon name="search-x" class="size-6 text-muted" />
                                <span class="text-sm text-muted">No results found</span>
                            </div>
                        </template>

                        {{-- Results list --}}
                        <ul x-show="results.length > 0" class="menu menu-sm p-1 gap-0.5 w-full">
                            <template x-for="(item, idx) in results" :key="item.web_url ?? idx">
                                <li>
                                    <a
                                        :href="item.web_url"
                                        role="option"
                                        data-live-search-item
                                        class="grid grid-cols-[auto_1fr] items-center gap-x-2 rounded-lg px-3 py-2 text-sm w-full"
                                        :class="{ 'bg-base-200': idx === activeIndex }"
                                        x-on:click.prevent="selectItem(idx)"
                                        x-on:mouseenter="activeIndex = idx"
                                    >
                                        <span x-show="item.type_label" x-text="item.type_label" class="text-xs text-muted truncate max-w-20"></span>
                                        <span x-show="!item.type_label">&nbsp;</span>
                                        <div class="flex items-center gap-2 min-w-0">
                                            <x-icon name="arrow-right" class="size-3.5 shrink-0 text-muted" />
                                            <span x-text="item.name ?? item.title" class="truncate"></span>
                                            <span
                                                x-show="itemLabel(item)"
                                                class="text-xs text-muted shrink-0 pl-1"
                                                x-text="itemLabel(item)"
                                            ></span>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
                @if ($helpText)
                    <p class="text-xs text-accent-content/40">{!! $helpText !!}</p>
                @endif
            </fieldset>
        </form>
    </div>
@endif
