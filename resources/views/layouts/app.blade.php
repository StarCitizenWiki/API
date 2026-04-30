@php use Illuminate\Support\Facades\Route; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
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
                    <div class="flex-none ">
                        <label for="app-drawer" class="btn btn-square btn-ghost" aria-label="Open menu">
                            <x-icon name="menu" class="size-5" />
                        </label>
                    </div>

                    <div class="flex-1">
                        @php
                            $homeUrl = Route::has('home')
                                ? route('home')
                                : url('/');
                        @endphp
                        <a class="btn btn-ghost text-lg" href="{{ $homeUrl }}">
                            {{ config('app.name', 'Laravel') }}
                        </a>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($gameVersions->isNotEmpty())
                            <form method="POST" action="{{ route('game-version.select') }}" class="hidden items-center gap-2 md:flex">
                                @csrf
                                <input type="hidden" name="redirect" value="{{ url()->full() }}">
                                <label class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Version</label>
                                <select name="version" data-testid="game-version-select-desktop" class="select select-bordered select-sm" onchange="this.form.submit()">
                                    @foreach ($gameVersions as $version)
                                        <option data-testid="game-version-option-desktop-{{ $version->code }}" value="{{ $version->code }}" @selected($selectedGameVersionCode === $version->code)>
                                            {{ $version->code }}@if ($version->channel) · {{ strtoupper($version->channel) }}@endif
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif

                        <label class="swap swap-rotate btn btn-ghost btn-square">
                            <input type="checkbox" class="theme-toggle" data-theme-toggle="dark" aria-label="Toggle dark mode">
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
                                            <span class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Version</span>
                                            <select name="version" data-testid="game-version-select-mobile" class="select select-bordered select-sm" onchange="this.form.submit()">
                                                @foreach ($gameVersions as $version)
                                                    <option data-testid="game-version-option-mobile-{{ $version->code }}" value="{{ $version->code }}" @selected($selectedGameVersionCode === $version->code)>
                                                        {{ $version->code }}@if ($version->channel) · {{ strtoupper($version->channel) }}@endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </li>
                                    <li><div class="divider my-1"></div></li>
                                @endif

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
                </nav>

                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10">
                    @yield('content')
                </main>
            </div>

            <nav class="drawer-side">
                <label for="app-drawer" class="drawer-overlay"></label>
                <aside class="min-h-full w-72 bg-base-100 px-3 py-5 pt-20">
                    <x-app.sidemenu>
                        <x-app.main-sidemenu />
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
