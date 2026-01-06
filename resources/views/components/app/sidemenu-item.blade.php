@props([
    'route' => null,
    'routeIs' => null,
    'href' => null,
    'params' => [],
    'versionCode' => null,
    'withVersion' => true,
])

@php
    $resolvedVersionCode = $versionCode
        ?? ($selectedGameVersionCode ?? null)
        ?? session('game_version_code')
        ?? request()->query('version');

    $resolvedHref = $href;
    $routeParams = $params;

    if ($route) {
        if ($withVersion && $resolvedVersionCode) {
            $routeParams = array_merge($routeParams, ['version' => $resolvedVersionCode]);
        }

        $resolvedHref = route($route, $routeParams);
    } elseif ($resolvedHref && $withVersion && $resolvedVersionCode) {
        $resolvedHref = url()->query($resolvedHref, ['version' => $resolvedVersionCode]);
    }

    $isActive = false;

    if ($routeIs) {
        $isActive = request()->routeIs($routeIs);
    } elseif ($route) {
        $isActive = request()->routeIs($route);
    }
@endphp

<li>
    <a href="{{ $resolvedHref }}" @class(['active' => $isActive])>
        @isset($icon)
            <span class="text-base-content/70">
                {{ $icon }}
            </span>
        @endisset
        <span>{{ $slot }}</span>
    </a>
</li>
