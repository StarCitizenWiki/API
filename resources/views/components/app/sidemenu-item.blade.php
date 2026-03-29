@php use Illuminate\Support\Facades\Route; @endphp
@props([
    'route' => null,
    'routeIs' => null,
    'href' => null,
    'params' => [],
    'activeFiltersAny' => [],
    'activeWhenFiltersEmpty' => false,
    'versionCode' => null,
    'withVersion' => true,
    'collapsible' => false,
    'defaultOpen' => false,
])

@php
    $routeIsPatterns = match (true) {
        is_array($routeIs) => $routeIs,
        $routeIs === null => [],
        default => [$routeIs],
    };

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
    $queryFilters = request()->query('filter');
    $resolvedQueryFilters = is_array($queryFilters) ? $queryFilters : [];
    $hasQueryFilters = $resolvedQueryFilters !== [];
    $hasCustomActive = $activeWhenFiltersEmpty || $activeFiltersAny !== [];

    if ($routeIsPatterns !== []) {
        $isActive = request()->routeIs(...$routeIsPatterns);
    } elseif ($route) {
        $isActive = request()->routeIs($route);

        if ($isActive && !empty($params)) {
            $currentRoute = request()->route();
            foreach ($params as $key => $value) {
                $routeValue = $currentRoute?->parameter($key);

                if ($routeValue !== null) {
                    if ((string) $routeValue !== (string) $value) {
                        $isActive = false;
                        break;
                    }

                    continue;
                }

                $queryValue = request()->query($key);

                if (is_array($value)) {
                    if (! is_array($queryValue)) {
                        $isActive = false;
                        break;
                    }

                    foreach ($value as $nestedKey => $nestedValue) {
                        if (! array_key_exists($nestedKey, $queryValue)) {
                            $isActive = false;
                            break 2;
                        }

                        if ((string) $queryValue[$nestedKey] !== (string) $nestedValue) {
                            $isActive = false;
                            break 2;
                        }
                    }

                    continue;
                }

                if ((string) $queryValue !== (string) $value) {
                    $isActive = false;
                    break;
                }
            }
        }
    }

    if ($route && $activeWhenFiltersEmpty) {
        $isActive = request()->routeIs($route) && ! $hasQueryFilters;
    } elseif ($route && $activeFiltersAny !== []) {
        $matchesAny = false;

        if ($hasQueryFilters) {
            foreach ($activeFiltersAny as $filterKey => $allowed) {
                if (! array_key_exists($filterKey, $resolvedQueryFilters)) {
                    continue;
                }

                $currentValue = $resolvedQueryFilters[$filterKey];
                $currentValues = is_array($currentValue)
                    ? $currentValue
                    : array_filter(array_map('trim', explode(',', (string) $currentValue)));

                $allowedValues = is_array($allowed) ? $allowed : [$allowed];

                foreach ($allowedValues as $allowedValue) {
                    if (in_array((string) $allowedValue, array_map('strval', $currentValues), true)) {
                        $matchesAny = true;
                        break 2;
                    }
                }
            }
        }

        $isActive = request()->routeIs($route) && $matchesAny;
    }

    if (!$isActive && $route && $collapsible && ! $hasCustomActive) {
        $routeParts = explode('.', $route);
        if (count($routeParts) > 1) {
            array_pop($routeParts);
            $basePattern = implode('.', $routeParts) . '.*';
            $isActive = request()->routeIs($basePattern);
        } else {
            $isActive = request()->routeIs($route . '.*');
        }
    }

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
            <ul class="menu">
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
