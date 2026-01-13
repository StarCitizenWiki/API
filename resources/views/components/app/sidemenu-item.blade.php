@php use Illuminate\Support\Facades\Route; @endphp
@props([
    'route' => null,
    'routeIs' => null,
    'href' => null,
    'params' => [],
    'versionCode' => null,
    'withVersion' => true,
    'collapsible' => false,
    'defaultOpen' => false,
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

        if ($isActive && !empty($params)) {
            $currentRoute = request()->route();
            foreach ($params as $key => $value) {
                if ($currentRoute->parameter($key) !== $value) {
                    $isActive = false;
                    break;
                }
            }
        }
    }

    // Check if any child should be active (for collapsible parent items)
    // We check for a broader pattern by extracting the base route prefix
    if (!$isActive && $route && $collapsible) {
        // Extract the base pattern (e.g., 'web.items' from 'web.items.index')
        $routeParts = explode('.', $route);
        if (count($routeParts) > 1) {
            // Try matching with the parent pattern (e.g., 'web.items.*')
            array_pop($routeParts);
            $basePattern = implode('.', $routeParts) . '.*';
            $isActive = request()->routeIs($basePattern);
        } else {
            // Fallback to exact pattern
            $isActive = request()->routeIs($route . '.*');
        }
    }

       // @dump($route, $isActive, request()->routeIs($route), Route::currentRouteName());

    $iconClass = $isActive ? 'menu-active-fg' : 'text-base-content/70';
@endphp

@if ($collapsible && isset($children) && !empty(trim((string) $children)))
    <li>
        <details @class(['collapse', 'collapse-arrow']) {{ ($defaultOpen || $isActive) ? 'open' : '' }}>
            <summary @class(['menu-item', 'menu-active' => $isActive, 'flex'])>
                @isset($icon)
                    <span class="{{ $iconClass }}">
                        {{ $icon }}
                    </span>
                @endisset
                <span class="mr-auto">{{ $slot }}</span>
            </summary>
            <ul class="menu menu-sm">
                {{ $children }}
            </ul>
        </details>
    </li>
@else
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
@endif
