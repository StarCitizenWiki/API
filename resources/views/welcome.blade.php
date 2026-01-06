@extends('layouts.app')

@section('title', 'Welcome')
@section('meta_description', 'Welcome to the API dashboard.')

@push('meta')
    <meta property="og:title" content="Welcome">
    <meta property="og:description" content="Welcome to the API dashboard.">
@endpush

@section('sidemenu')
    <x-app.sidemenu-group title="Overview">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12l9-9 9 9" />
                <path d="M9 21V9h6v12" />
            </svg>
        </x-slot:icon>
        <x-app.sidemenu-item :route="'home'">Home</x-app.sidemenu-item>
    </x-app.sidemenu-group>

    <x-app.sidemenu-group title="Explore">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" />
            </svg>
        </x-slot:icon>
        <x-app.sidemenu-item href="/docs" :with-version="false">API Docs</x-app.sidemenu-item>
    </x-app.sidemenu-group>
@endsection

@section('content')
    <div class="flex flex-col gap-6">
        <div class="hero rounded-3xl bg-base-200">
            <div class="hero-content flex-col gap-6 text-center lg:flex-row lg:text-left">
                <div class="max-w-xl">
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Build with confidence.</h1>
                    <p class="mt-2 text-base-content/70">
                        Use the layout shell to compose navigation, game version selection, and SEO metadata per route.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a class="btn btn-primary" href="#">Get Started</a>
                        <a class="btn btn-ghost" href="#">View Changelog</a>
                    </div>
                </div>
                <div class="card w-full max-w-sm border border-base-300 bg-base-100 shadow">
                    <div class="card-body gap-4">
                        <div class="flex items-center justify-between">
                            <h2 class="card-title text-base">Active Game Version</h2>
                            <span class="badge badge-outline">{{ $selectedGameVersion?->code ?? 'n/a' }}</span>
                        </div>
                        <p class="text-sm text-base-content/70">
                            Switch versions in the top bar to update the request session and URL query.
                        </p>
                        <div class="card-actions justify-end">
                            <button class="btn btn-sm">View Details</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="stat rounded-2xl border border-base-300 bg-base-100">
                <div class="stat-title">Routes</div>
                <div class="stat-value text-2xl">24</div>
                <div class="stat-desc">With configurable SEO tags</div>
            </div>
            <div class="stat rounded-2xl border border-base-300 bg-base-100">
                <div class="stat-title">Menus</div>
                <div class="stat-value text-2xl">3</div>
                <div class="stat-desc">Grouped with icons and active states</div>
            </div>
            <div class="stat rounded-2xl border border-base-300 bg-base-100">
                <div class="stat-title">Theme</div>
                <div class="stat-value text-2xl">DaisyUI</div>
                <div class="stat-desc">Light and dark modes</div>
            </div>
        </div>
    </div>
@endsection
