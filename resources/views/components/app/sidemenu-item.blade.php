@php use Illuminate\Support\Facades\Route; @endphp
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

       // @dump($route, $isActive, request()->routeIs($route), Route::currentRouteName());

    $iconClass = $isActive ? 'text-primary-content' : 'text-base-content/70';
@endphp

<li>
    <a href="{{ $resolvedHref }}" @class(['menu-active' => $isActive])>
        @isset($icon)
            <span class="{{ $iconClass }}">
                {{ $icon }}
            </span>
        @endisset
        <span>{{ $slot }}</span>
    </a>
</li>
