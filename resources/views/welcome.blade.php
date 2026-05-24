@extends('layouts.app')

@section('title', 'Star Citizen Wiki API')
@section('meta_description', 'Welcome to the Star Citizen Wiki API. Your Verse Data Mine.')

@push('meta')
    <meta property="og:title" content="Star Citizen Wiki API">
    <meta property="og:description" content="Welcome to the Star Citizen Wiki API. Your Verse Data Mine.">
@endpush

@section('content')
    @php
        $categoryColorClasses = [
            'primary' => ['bg' => 'bg-primary/10', 'text' => 'text-primary'],
            'secondary' => ['bg' => 'bg-secondary/10', 'text' => 'text-secondary'],
            'accent' => ['bg' => 'bg-accent/10', 'text' => 'text-accent'],
            'info' => ['bg' => 'bg-info/10', 'text' => 'text-info'],
        ];

        $categorySections = [
            [
                'title' => 'Game Data',
                'category' => 'In-game database',
                'icon' => 'rocket',
                'color' => 'primary',
                'groups' => [
                    [
                        'title' => 'Vehicles & Components',
                        'links' => [
                            ['label' => 'Ships & Vehicles', 'href' => route('web.vehicles.index'), 'testId' => 'welcome-vehicles-link'],
                            ['label' => 'All Components', 'href' => route('web.items.index', ['filter' => ['category' => 'vehicle-components']])],
                            ['label' => 'Shields', 'href' => route('web.items.index', ['filter' => ['type' => 'Shield']])],
                            ['label' => 'Quantum Drives', 'href' => route('web.items.index', ['filter' => ['type' => 'QuantumDrive']])],
                            ['label' => 'Power Plants', 'href' => route('web.items.index', ['filter' => ['type' => 'PowerPlant']])],
                            ['label' => 'Coolers', 'href' => route('web.items.index', ['filter' => ['type' => 'Cooler']])],
                        ],
                    ],
                    [
                        'title' => 'Items',
                        'links' => [
                            ['label' => 'All Items', 'href' => route('web.items.index')],
                            ['label' => 'Blueprints', 'href' => route('web.blueprints.index')],
                            ['label' => 'Personal Weapons', 'href' => route('web.items.index', ['filter' => ['type' => 'WeaponPersonal']])],
                            ['label' => 'Personal Armor', 'href' => route('web.items.index', ['filter' => ['category' => 'fps-armor']])],
                            ['label' => 'Weapon Attachments', 'href' => route('web.items.index', ['filter' => ['category' => 'weapon-attachments']])],
                        ],
                    ],
                    [
                        'title' => 'Operations',
                        'links' => [
                            ['label' => 'Locations', 'href' => route('web.locations.index')],
                            ['label' => 'Commodities', 'href' => route('web.commodities.index')],
                            ['label' => 'Missions', 'href' => route('web.missions.index')],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'RSI Archive',
                'category' => 'Website data',
                'icon' => 'archive',
                'color' => 'secondary',
                'groups' => [
                    [
                        'title' => 'Comm-Link',
                        'links' => [
                            ['label' => 'Comm-Links', 'href' => route('web.comm-links.index')],
                            ['label' => 'Search Comm-Links', 'href' => route('web.comm-links.search'), 'testId' => 'welcome-comm-links-search-link'],
                            ['label' => 'Comm-Link Images', 'href' => route('web.comm-links.images.index')],
                        ],
                    ],
                    [
                        'title' => 'Ship Matrix',
                        'links' => [
                            ['label' => 'Ships & Vehicles', 'href' => route('web.ship-matrix.vehicles.index')],
                        ],
                    ],
                    [
                        'title' => 'Stats',
                        'links' => [
                            ['label' => 'Stats', 'href' => route('web.stats.index')],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Lore & Starmap',
                'category' => 'World reference',
                'icon' => 'book-open',
                'color' => 'accent',
                'groups' => [
                    [
                        'title' => 'Galactapedia',
                        'links' => [
                            ['label' => 'Articles', 'href' => route('web.galactapedia.index')],
                        ],
                    ],
                    [
                        'title' => 'Starmap',
                        'links' => [
                            ['label' => 'Systems', 'href' => route('web.starmap.systems.index'), 'testId' => 'welcome-starmap-systems-link'],
                            ['label' => 'Celestial Objects', 'href' => route('web.starmap.celestial-objects.index')],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Developers',
                'category' => 'API access',
                'icon' => 'code-2',
                'color' => 'info',
                'groups' => [
                    [
                        'title' => 'Resources',
                        'links' => [
                            ['label' => 'Developer Quickstart', 'href' => route('developers.index'), 'testId' => 'welcome-developers-link'],
                            ['label' => 'API Documentation', 'href' => 'https://docs.star-citizen.wiki', 'external' => true],
                            ['label' => 'Source Code', 'href' => 'https://github.com/StarCitizenWiki/API', 'external' => true],
                        ],
                    ],
                ],
            ],
        ];
    @endphp

    <div class="mx-auto max-w-6xl py-8 lg:py-12">
        <div class="md:grid md:grid-cols-2 md:gap-6 md:items-center">
            <div class="mb-6 md:mb-0">
                <span class="badge badge-ghost mb-3 text-xs tracking-widest uppercase">Verse Data Mine</span>
                <h1 class="text-3xl font-bold sm:text-4xl mb-3" data-testid="welcome-page-title">
                    Star Citizen Wiki API
                </h1>
                <p class="text-subtle mb-5 max-w-md">
                    Browse game data, RSI archives, and starmap systems. Powering starcitizen.tools.
                </p>
                <div class="flex flex-row flex-wrap gap-2">
                    <a href="{{ route('developers.index') }}" class="btn btn-primary btn-sm" data-testid="welcome-developers-link">
                        <x-icon name="zap" class="size-4" />
                        Developer Quickstart
                    </a>
                    <a href="https://docs.star-citizen.wiki" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">
                        <x-icon name="book-open" class="size-4" />
                        API Documentation
                        <x-icon name="external-link" class="size-3 opacity-60" />
                    </a>
                </div>
            </div>

            <div>
                <div class="rounded-box border border-base-300 bg-base-100 p-4 sm:p-5" data-testid="welcome-search-items">
                    <form method="GET" action="{{ route('web.items.index') }}" class="space-y-3">
                        <h2 class="text-base font-semibold">Search the Verse</h2>

                        <div
                            x-data="liveSearch('/api/search')"
                            x-init="init()"
                            x-on:click.window="closeOnOutside($event)"
                            class="flex flex-col gap-3 sm:flex-row"
                        >
                            <label class="input input-bordered flex w-full items-center gap-2 bg-base-100">
                                <x-icon name="search" class="size-4 text-subtle" x-show="!loading" />
                                <span class="loading loading-spinner loading-xs text-subtle" x-show="loading"></span>
                                <input
                                    x-ref="input"
                                    type="search"
                                    name="filter[name]"
                                    class="w-full"
                                    placeholder="Search ships, items, locations..."
                                    x-model="query"
                                    x-on:input="search()"
                                    x-on:keydown="navigate($event)"
                                    x-on:focus="results.length && query.trim().length >= 2 && (open = true)"
                                    autocomplete="off"
                                />
                            </label>
                            <button class="btn btn-primary sm:shrink-0 hidden sm:block" type="submit">Search</button>

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
                                <template x-if="results.length === 0">
                                    <div class="flex flex-col items-center gap-2 px-4 py-6 text-center">
                                        <x-icon name="search-x" class="size-6 text-muted" />
                                        <span class="text-sm text-muted">No results found</span>
                                    </div>
                                </template>

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

                        <p class="text-xs text-subtle">
                            Or link directly: <code class="rounded bg-base-100 px-1 py-0.5">/search/{name}</code>, <code class="rounded bg-base-100 px-1 py-0.5">/search/{class_name}</code>, or <code class="rounded bg-base-100 px-1 py-0.5">/search/{uuid}</code>.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl flex flex-col gap-3 pb-6">
        @foreach ($categorySections as $section)
            <div class="rounded-box border border-base-300 bg-base-100 p-4 sm:p-5">
                <div class="flex flex-col sm:flex-row sm:items-start">
                    <div class="mb-3 flex items-center gap-2.5 sm:mb-0 sm:w-56 sm:shrink-0">
                        @php $colorClasses = $categoryColorClasses[$section['color']] ?? $categoryColorClasses['primary']; @endphp
                        <div class="rounded-lg {{ $colorClasses['bg'] }} p-2">
                            <x-icon name="{{ $section['icon'] }}" class="size-5 {{ $colorClasses['text'] }}" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold">{{ $section['title'] }}</h2>
                            <span class="text-xs text-muted">{{ $section['category'] }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 flex-1 md:grid-cols-3">
                        @foreach ($section['groups'] as $group)
                            <div class="flex flex-col">
                                <span class="text-xs font-medium uppercase tracking-wider text-muted mb-1">{{ $group['title'] }}</span>
                                @foreach ($group['links'] as $link)
                                    <a
                                        href="{{ $link['href'] }}"
                                        @isset($link['testId']) data-testid="{{ $link['testId'] }}" @endisset
                                        @if (($link['external'] ?? false)) target="_blank" rel="noopener noreferrer" @endif
                                        class="text-sm link-primary hover:underline inline-flex items-center gap-1 py-0.5"
                                    >
                                        {{ $link['label'] }}
                                        @if (($link['external'] ?? false))
                                            <x-icon name="external-link" class="size-3 opacity-60" />
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mx-auto max-w-6xl pb-10">
        <div class="flex flex-col items-center gap-4 rounded-box border border-base-300 p-4 sm:flex-row sm:p-5">
            <img src="{{ asset('MadeByTheCommunity_White.png') }}" alt="Made by the Community" class="h-10 shrink-0" />
            <p class="text-xs text-center text-subtle sm:text-start">
                This is an unofficial Star Citizen fan site, not affiliated with the
                <a href="https://robertsspaceindustries.com" target="_blank" rel="noopener noreferrer" class="link-primary">Cloud Imperium</a>
                group of companies. All content on this site not authored by its host or users are property of their respective owners.
                Visit the <a href="https://robertsspaceindustries.com" target="_blank" rel="noopener noreferrer" class="link-primary">official Star Citizen website</a>.
            </p>
        </div>
    </div>
@endsection
