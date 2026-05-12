@php use Illuminate\Support\Facades\Route; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeToggle" x-init="init()" x-bind:data-theme="isDark ? darkTheme : lightTheme" x-effect="localStorage.setItem('theme', isDark ? darkTheme : lightTheme)">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ trim($__env->yieldContent('title', config('app.name', 'Laravel'))) }}</title>
        <meta name="description" content="{{ trim($__env->yieldContent('meta_description', config('app.name', 'Laravel'))) }}">

        @yield('meta')
        @stack('meta')

        <script>
            window.AppThemes = {
                light: @json(config('app.ui.themes.light', 'nord')),
                dark: @json(config('app.ui.themes.dark', 'night')),
            };

            (function () {
                const storedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = storedTheme ?? (prefersDark ? window.AppThemes.dark : window.AppThemes.light);
                document.documentElement.setAttribute('data-theme', theme);
            })();
        </script>

        @if (config('services.plausible.enabled') && config('services.plausible.domain'))
            <script defer data-domain="{{ config('services.plausible.domain') }}" src="{!! config('services.plausible.tracking_script', '') !!}"></script>
        @endif

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @stack('styles')
    </head>
    <body class="min-h-screen bg-base-200 text-base-content">
        <div class="drawer ">
            <input id="app-drawer" type="checkbox" class="drawer-toggle" />

            <div class="drawer-content flex min-h-screen flex-col">
                <nav class="navbar sticky top-0 z-30 border-b border-base-300 bg-base-100 backdrop-blur">
                    <div class="grid w-full grid-cols-[1fr_8fr_1fr] sm:grid-cols-[1fr_2fr_1fr] items-center gap-2 px-1">
                        <div class="flex items-center gap-1">
                            <label for="app-drawer" class="btn btn-square btn-ghost" aria-label="Open menu">
                                <x-icon name="menu" class="size-5" />
                            </label>

                            @php
                                $homeUrl = Route::has('home')
                                    ? route('home')
                                    : url('/');
                            @endphp
                            <a class="btn btn-ghost text-lg hidden md:flex" href="{{ $homeUrl }}">
{{--                                <img src="{{ asset('icon.png') }}" alt="" class="size-6">--}}
                                <span class="hidden md:inline">{{ config('app.name', 'Laravel') }}</span>
                            </a>
                        </div>

                        <div
                            x-data="liveSearch('/api/search')"
                            x-init="init()"
                            x-on:click.window="closeOnOutside($event)"
                            class="w-full min-w-0 max-w-xl px-2 place-self-center"
                        >
                            <label class="input input-bordered input-sm flex w-full items-center gap-2">
                                <x-icon name="search" class="size-4 text-subtle shrink-0" />
                                <input
                                    x-ref="input"
                                    type="search"
                                    class="grow text-sm"
                                    placeholder="Search the verse..."
                                    data-testid="header-search-input"
                                    x-model="query"
                                    x-on:input="search()"
                                    x-on:keydown="navigate($event)"
                                    x-on:focus="results.length && query.trim().length >= 2 && (open = true)"
                                    autocomplete="off"
                                />
                            </label>

                            {{-- Live search dropdown --}}
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

                        <div class="flex items-center justify-end gap-2">
                            @if ($gameVersions->isNotEmpty())
                                <form method="POST" action="{{ route('game-version.select') }}" class="hidden items-center gap-2 md:flex">
                                    @csrf
                                    <input type="hidden" name="redirect" value="{{ url()->full() }}">
                                    <select name="version" data-testid="game-version-select-desktop" class="select select-bordered select-sm" onchange="this.form.submit()">
                                        @foreach ($gameVersions as $version)
                                            <option data-testid="game-version-option-desktop-{{ $version->code }}" value="{{ $version->code }}" @selected($selectedGameVersionCode === $version->code)>
                                                {{ $version->code }}@if ($version->channel) · {{ strtoupper($version->channel) }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif

                            <label class="swap swap-rotate btn btn-ghost btn-square hidden md:inline-grid">
                                <input type="checkbox" class="theme-toggle" aria-label="Toggle dark mode" x-model="isDark">
                                <x-icon name="sun" class="swap-off size-5" />
                                <x-icon name="moon" class="swap-on size-5" />
                            </label>

                            <div class="hidden items-center gap-1 md:flex">
                                @guest
                                    @if (Route::has('login'))
                                        <a class="btn btn-ghost btn-sm" href="{{ route('login') }}">Login</a>
                                    @endif
                                    @if (Route::has('register'))
                                        <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Register</a>
                                    @endif
                                @endguest

                                @auth
                                    @if (Route::has('profile'))
                                        <a class="btn btn-ghost btn-sm" data-testid="app-shell-profile-link" href="{{ route('profile') }}">Profile</a>
                                    @endif
                                    @if (Route::has('logout'))
                                        <form method="POST" action="{{ route('logout') }}" data-testid="app-shell-logout-form">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm" data-testid="app-shell-logout-submit">Logout</button>
                                        </form>
                                    @endif
                                @endauth
                            </div>

                            <div class="dropdown dropdown-end md:hidden">
                                <button tabindex="0" role="button" class="btn btn-ghost btn-square" aria-label="Open menu">
                                    <x-icon name="more-vertical" class="size-5" />
                                </button>
                                <ul tabindex="0" class="menu dropdown-content z-1 mt-2 w-64 rounded-box border border-base-200 bg-base-100 p-2 shadow">
                                    @if ($gameVersions->isNotEmpty())
                                        <li>
                                            <form method="POST" action="{{ route('game-version.select') }}" class="flex flex-col gap-2">
                                                @csrf
                                                <input type="hidden" name="redirect" value="{{ url()->full() }}">
                                                <select name="version" data-testid="game-version-select-mobile" class="select select-bordered select-sm" onchange="this.form.submit()">
                                                    @foreach ($gameVersions as $version)
                                                        <option data-testid="game-version-option-mobile-{{ $version->code }}" value="{{ $version->code }}" @selected($selectedGameVersionCode === $version->code)>
                                                            {{ $version->code }}@if ($version->channel) · {{ strtoupper($version->channel) }}@endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </li>
                                    @endif

                                    <li>
                                        <label class="swap place-content-start">
                                            <input type="checkbox" class="theme-toggle" aria-label="Toggle dark mode" x-model="isDark">
                                            <span class="swap-off flex gap-2"><x-icon name="sun" class="size-5" />Light</span>
                                            <span class="swap-on flex gap-2"><x-icon name="moon" class="size-5" />Dark</span>
                                        </label>
                                    </li>

                                    @guest
                                        @if (Route::has('login'))
                                            <li><a href="{{ route('login') }}">Login</a></li>
                                        @endif
                                        @if (Route::has('register'))
                                            <li><a href="{{ route('register') }}">Register</a></li>
                                        @endif
                                    @endguest

                                    @auth
                                        @if (Route::has('profile'))
                                            <li><a href="{{ route('profile') }}">Profile</a></li>
                                        @endif
                                        @if (Route::has('logout'))
                                            <li>
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <button type="submit" class="w-full text-left">Logout</button>
                                                </form>
                                            </li>
                                        @endif
                                    @endauth
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                @if ($gameVersions->isNotEmpty() && $selectedGameVersion && ! $selectedGameVersion->is_default)
                    <div role="alert" class="alert alert-warning alert-soft text-xs text-center p-1 justify-center">
                        <span>
                            You are viewing data from version
                            <strong>{{ $selectedGameVersion->code }}</strong>,
                            which may be outdated.
                        </span>
                    </div>
                @endif

                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10">
                    @yield('content')
                </main>
            </div>

            <nav class="drawer-side">
                <label for="app-drawer" class="drawer-overlay"></label>
                <aside class="min-h-full w-72 bg-base-100 px-3 py-5 pt-20">
                    <x-app.sidemenu>
                        <x-app.main-sidemenu :changelogVersionCode="$changelogVersionCode"/>
                        @hasSection('sidemenu')
                            @yield('sidemenu')
                        @endif
                    </x-app.sidemenu>
                </aside>
            </nav>
        </div>

        @stack('scripts')
    </body>
</html>
