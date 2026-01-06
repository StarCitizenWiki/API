<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ trim($__env->yieldContent('title', config('app.name', 'Laravel'))) }}</title>
        <meta name="description" content="{{ trim($__env->yieldContent('meta_description', config('app.name', 'Laravel'))) }}">

        @yield('meta')
        @stack('meta')

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <script>
            (function () {
                const storedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = storedTheme ?? (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            })();
        </script>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @stack('styles')
    </head>
    <body class="min-h-screen bg-base-100 text-base-content">
        <div class="drawer lg:drawer-open">
            <input id="app-drawer" type="checkbox" class="drawer-toggle" />

            <div class="drawer-content flex min-h-screen flex-col">
                <div class="navbar sticky top-0 z-30 border-b border-base-200 bg-base-100/90 backdrop-blur">
                    <div class="flex-none lg:hidden">
                        <label for="app-drawer" class="btn btn-square btn-ghost" aria-label="Open menu">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </label>
                    </div>

                    <div class="flex-1">
                        @php
                            $homeUrl = Route::has('home')
                                ? route('home', $selectedGameVersionCode ? ['version' => $selectedGameVersionCode] : [])
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
                                <select name="version" class="select select-bordered select-sm" onchange="this.form.submit()">
                                    @foreach ($gameVersions as $version)
                                        <option value="{{ $version->code }}" @selected($selectedGameVersionCode === $version->code)>
                                            {{ $version->code }}@if ($version->channel) · {{ strtoupper($version->channel) }}@endif
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif

                        <label class="swap swap-rotate btn btn-ghost btn-square">
                            <input type="checkbox" class="theme-toggle" data-theme-toggle="dark" aria-label="Toggle dark mode">
                            <svg class="swap-off size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4" />
                                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                            </svg>
                            <svg class="swap-on size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z" />
                            </svg>
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
                                @if (Route::has('profile.show'))
                                    <a class="btn btn-ghost btn-sm" href="{{ route('profile.show') }}">Profile</a>
                                @endif
                                @if (Route::has('logout'))
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-sm">Logout</button>
                                    </form>
                                @endif
                            @endauth
                        </div>

                        <div class="dropdown dropdown-end md:hidden">
                            <button tabindex="0" role="button" class="btn btn-ghost btn-square" aria-label="Open menu">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 6h0M12 12h0M12 18h0" />
                                </svg>
                            </button>
                            <ul tabindex="0" class="menu dropdown-content z-[1] mt-2 w-64 rounded-box border border-base-200 bg-base-100 p-2 shadow">
                                @if ($gameVersions->isNotEmpty())
                                    <li>
                                        <form method="POST" action="{{ route('game-version.select') }}" class="flex flex-col gap-2">
                                            @csrf
                                            <input type="hidden" name="redirect" value="{{ url()->full() }}">
                                            <span class="text-xs font-semibold uppercase tracking-wide text-base-content/60">Version</span>
                                            <select name="version" class="select select-bordered select-sm" onchange="this.form.submit()">
                                                @foreach ($gameVersions as $version)
                                                    <option value="{{ $version->code }}" @selected($selectedGameVersionCode === $version->code)>
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
                                    @if (Route::has('profile.show'))
                                        <li><a href="{{ route('profile.show') }}">Profile</a></li>
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

                <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10">
                    @yield('content')
                </main>
            </div>

            <div class="drawer-side">
                <label for="app-drawer" class="drawer-overlay"></label>
                <aside class="min-h-full w-72 bg-base-200 px-3 py-5">
                    <x-app.sidemenu>
                        @hasSection('sidemenu')
                            @yield('sidemenu')
                        @else
                            <x-app.sidemenu-group title="Getting Started">
                                <x-app.sidemenu-item :route="'home'">Home</x-app.sidemenu-item>
                            </x-app.sidemenu-group>
                        @endif
                    </x-app.sidemenu>
                </aside>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
