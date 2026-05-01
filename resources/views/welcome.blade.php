@extends('layouts.app')

@section('title', 'Star Citizen Wiki API')
@section('meta_description', 'Welcome to the Star Citizen Wiki API. Your Verse Data Mine.')

@push('meta')
    <meta property="og:title" content="Star Citizen Wiki API">
    <meta property="og:description" content="Welcome to the Star Citizen Wiki API. Your Verse Data Mine.">
@endpush

@section('content')
    <div class="flex flex-col gap-6">
        <div class="hero rounded-3xl bg-base-200">
            <div class="hero-content flex-col gap text-center lg:flex-row lg:text-left">
                <div class="max-w-xl shrink-0">
                    <div class="min-w-0 flex-1 max-w-xl">
                        <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl" data-testid="welcome-page-title">
                            Star Citizen Wiki API
                        </h1>
                        <h2 class="text-xl tracking-tight">Verse Data Mine</h2>
                    </div>
                </div>
            </div>
        </div>

        <div data-testid="welcome-search-items">
            <x-resource-search
                title="Search the Verse"
                description="Find items, vehicles, locations, commodities, blueprints, and missions."
                :route="route('web.items.index')"
                placeholder="Search ships, items, locations..."
                apiEndpoint="/api/search"
                helpText="Direct link: <code>/search/{name}</code>, <code>/search/{class_name}</code>, or <code>/search/{uuid}</code> resolves to the matching entity page."
            />
        </div>

        <div class="flex flex-col gap-4">
            <div>
                <h2 class="text-lg font-semibold">Categories</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="card border border-base-300 bg-base-100 shadow">
                    <div class="card-body gap-4">
                        <h3 class="card-title text-base">Universe <span class="badge badge-primary badge-sm ml-auto">In-Game Data</span></h3>
                        <div class="grid grid-cols-2 gap-x-2">
                            <ul class="menu">
                                <li>
                                    <h2 class="menu-title">Vehicles</h2>
                                    <ul>
                                        <li><a data-testid="welcome-vehicles-link" href="{{ route('web.vehicles.index') }}">Ships & Vehicles</a></li>
                                        <li><a href="{{ route('web.items.index', ['filter' => ['category' => 'vehicle-components']]) }}">Components</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <h2 class="menu-title">Items</h2>
                                    <ul>
                                        <li><a href="{{ route('web.items.index') }}">All Items</a></li>
                                        <li><a href="{{ route('web.blueprints.index') }}">Blueprints</a></li>
                                        <li><a href="{{ route('web.items.index', ['filter' => ['type' => 'WeaponPersonal']]) }}">Personal Weapons</a></li>
                                        <li><a href="{{ route('web.items.index', ['filter' => ['category' => 'fps-armor']]) }}">Personal Armor</a></li>
                                        <li><a href="{{ route('web.items.index', ['filter' => ['category' => 'weapon-attachments']]) }}">Weapon Attachments</a></li>
                                    </ul>
                                </li>
                            </ul>
                            <ul class="menu">
                                <li>
                                    <h2 class="menu-title">Locations</h2>
                                    <ul>
                                        <li><a href="{{ route('web.locations.index') }}">Locations</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <h2 class="menu-title">Commodities</h2>
                                    <ul>
                                        <li><a href="{{ route('web.commodities.index') }}">Commodities</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <h2 class="menu-title">Missions</h2>
                                    <ul>
                                        <li><a href="{{ route('web.missions.index') }}">Missions</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card border border-base-300 bg-base-100 shadow">
                    <div class="card-body ">
                        <h3 class="card-title text-base">Communication <span class="badge badge-secondary badge-sm ml-auto">Website Data</span></h3>
                        <div class="flex flex-wrap">
                            <ul class="menu">
                                <li>
                                    <h2 class="menu-title">Comm-Link</h2>
                                    <ul>
                                        <li><a href="{{ route('web.comm-links.index') }}">Comm-Links</a></li>
                                        <li><a data-testid="welcome-comm-links-search-link" href="{{ route('web.comm-links.search') }}">Search</a></li>
                                        <li><a href="{{ route('web.comm-links.images.index') }}">Comm-Link Images</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <h2 class="menu-title">Ship-Matrix</h2>
                                    <ul>

                                        <li><a href="{{ route('web.ship-matrix.vehicles.index') }}">Ships & Vehicles</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <h2 class="menu-title">Stats</h2>
                                    <ul>

                                        <li><a href="{{ route('web.stats.index') }}">Stats</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card border border-base-300 bg-base-100 shadow">
                    <div class="card-body gap-4">
                        <h3 class="card-title text-base">Lore</h3>
                        <div class="flex flex-wrap gap-2">
                            <ul class="menu">
                                <li>
                                    <h2 class="menu-title">Galactapedia</h2>
                                    <ul>
                                        <li><a href="{{ route('web.galactapedia.index') }}">Articles</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <h2 class="menu-title">Starmap</h2>
                                    <ul>
                                        <li><a data-testid="welcome-starmap-systems-link" href="{{ route('web.starmap.systems.index') }}">Systems</a></li>
                                        <li><a href="{{ route('web.starmap.celestial-objects.index') }}">Celestial Objects</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card border border-base-300 bg-base-100 shadow">
                    <div class="card-body gap-4">
                        <h3 class="card-title text-base">Explore</h3>
                        <div class="flex flex-wrap gap-2">
                            <a class="btn btn-sm btn-outline" href="https://docs.star-citizen.wiki">Api Documentation</a>
                            <a class="btn btn-sm btn-outline" href="https://github.com/StarCitizenWiki/API">Source Code</a>
                        </div>
                    </div>
                </div>

                @can('access-admin')
                    <div class="card border border-base-300 bg-base-100 shadow" data-testid="welcome-admin-card">
                        <div class="card-body gap-4">
                            <h3 class="card-title text-base">Home</h3>
                            <div class="flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline" data-testid="welcome-home-link" href="{{ route('home') }}">Home</a>
                                @auth
                                    @if (Route::has('admin.dashboard'))
                                        <a class="btn btn-sm btn-outline" data-testid="welcome-admin-link" href="{{ route('admin.dashboard') }}">Admin</a>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection
