@extends('layouts.app')

@section('title', 'Star Citizen Wiki API')
@section('meta_description', 'Welcome to the Star Citizen Wiki API. Your Verse Data Mine.')

@push('meta')
    <meta property="og:title" content="Star Citizen Wiki API">
    <meta property="og:description" content="Welcome to the Star Citizen Wiki API. Your Verse Data Mine.">
@endpush

@section('content')
    @php
        $categorySections = [
            [
                'title' => 'Game Data',
                'category' => 'In-game database',
                'groups' => [
                    [
                        'title' => 'Vehicles',
                        'links' => [
                            ['label' => 'Ships & Vehicles', 'href' => route('web.vehicles.index'), 'testId' => 'welcome-vehicles-link'],
                            ['label' => 'Components', 'href' => route('web.items.index', ['filter' => ['category' => 'vehicle-components']])],
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

    <div class="flex flex-col gap-8">
        <section class="card card-border bg-base-100 shadow-sm">
            <div class="card-body gap-6 p-6 sm:p-8">
                <div class="max-w-3xl space-y-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-primary">Verse Data Mine</p>
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-5xl" data-testid="welcome-page-title">
                        Star Citizen Wiki API
                    </h1>
                </div>

                <div class="rounded-box border border-base-300 bg-base-200 p-4 sm:p-5" data-testid="welcome-search-items">
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
                            <button class="btn btn-primary sm:shrink-0" type="submit">Search</button>

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

                <div class="flex flex-col gap-2 sm:flex-row">
                    <a class="btn btn-outline" href="{{ route('developers.index') }}">Developer Quickstart</a>
                    <a class="btn btn-outline" href="https://docs.star-citizen.wiki" rel="noopener noreferrer">API Documentation</a>
                </div>
            </div>
        </section>

        <section class="flex flex-col gap-4">

            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($categorySections as $section)
                    <article class="card card-border bg-base-100 shadow-sm">
                        <div class="card-body gap-3">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $section['category'] }}</p>
                                <h3 class="card-title text-base">{{ $section['title'] }}</h3>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($section['groups'] as $group)
                                    <div class="rounded-box border border-base-200 bg-base-200 p-2">
                                        <p class="px-3 pt-2 text-xs font-semibold uppercase tracking-wide text-muted">{{ $group['title'] }}</p>
                                        <ul class="menu menu-sm p-0">
                                            @foreach ($group['links'] as $link)
                                                <li>
                                                    <a
                                                        href="{{ $link['href'] }}"
                                                        class="link-primary"
                                                        @isset($link['testId']) data-testid="{{ $link['testId'] }}" @endisset
                                                        @if ($link['external'] ?? false) rel="noopener noreferrer" @endif
                                                    >
                                                        {{ $link['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </article>
                @endforeach

                @can('access-admin')
                    <article class="card card-border bg-base-100 shadow-sm" data-testid="welcome-admin-card">
                        <div class="card-body gap-4">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Authorized access</p>
                                <h3 class="card-title text-base">Admin</h3>
                                <p class="text-sm text-subtle">Open the application home or administration dashboard.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline" data-testid="welcome-home-link" href="{{ route('home') }}">Home</a>
                                @auth
                                    @if (Route::has('admin.dashboard'))
                                        <a class="btn btn-sm btn-outline" data-testid="welcome-admin-link" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </article>
                @endcan
            </div>
        </section>
    </div>
@endsection
