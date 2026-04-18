<?php

declare(strict_types=1);

namespace App\Support\Filters;

use Closure;
use Illuminate\Support\Facades\Cache;

final class FilterCache
{
    public const NAMESPACE_ITEMS = 'items';

    public const NAMESPACE_COMM_LINKS = 'comm-links';

    public const NAMESPACE_GALACTAPEDIA = 'galactapedia';

    public const NAMESPACE_STARSYSTEMS = 'starsystems';

    public const NAMESPACE_STARMAP_LOCATIONS = 'starmap-locations';

    public const NAMESPACE_SHIPMATRIX = 'shipmatrix';

    public const NAMESPACE_VEHICLES = 'vehicles';

    public const NAMESPACE_COMMODITIES = 'commodities';

    public const NAMESPACE_BLUEPRINTS = 'blueprints';

    public const NAMESPACE_MISSIONS = 'missions';

    public static function rememberForever(string $namespace, string $key, Closure $resolver): mixed
    {
        self::trackKey($namespace, $key);

        if (app()->environment('production')) {
            return Cache::rememberForever($key, $resolver);
        }

        return $resolver();
    }

    /**
     * @param  array<int, string>  $ignored
     */
    public static function hasEffectiveFilters(mixed $filters, array $ignored = []): bool
    {
        if (! is_array($filters) || $filters === []) {
            return false;
        }

        foreach ($filters as $field => $value) {
            if (is_string($field) && in_array($field, $ignored, true)) {
                continue;
            }

            if (is_array($value)) {
                if (self::hasEffectiveFilters($value)) {
                    return true;
                }

                continue;
            }

            if ($value !== null && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    public static function bust(string $namespace): void
    {
        $indexKey = self::indexKey($namespace);
        $keys = Cache::get($indexKey, []);

        if (! is_array($keys)) {
            Cache::forget($indexKey);

            return;
        }

        foreach ($keys as $key) {
            Cache::forget((string) $key);
        }

        Cache::forget($indexKey);
    }

    public static function itemsKey(?string $versionCode, string $category): string
    {
        return sprintf('filters:items:%s:%s', self::normalizeVersion($versionCode), $category);
    }

    public static function vehiclesKey(?string $versionCode, string $vehicleType): string
    {
        return sprintf('filters:vehicles:%s:%s', self::normalizeVersion($versionCode), $vehicleType);
    }

    public static function starmapLocationsKey(?string $versionCode): string
    {
        return sprintf('filters:starmap-locations:%s', self::normalizeVersion($versionCode));
    }

    public static function commLinksKey(bool $isAuthenticated): string
    {
        return sprintf('filters:comm-links:%s', $isAuthenticated ? 'auth' : 'guest');
    }

    public static function galactapediaKey(): string
    {
        return 'filters:galactapedia';
    }

    public static function starsystemsKey(): string
    {
        return 'filters:starsystems';
    }

    public static function commoditiesKey(?string $versionCode): string
    {
        return sprintf('filters:commodities:%s', self::normalizeVersion($versionCode));
    }

    public static function shipMatrixKey(): string
    {
        return 'filters:shipmatrix';
    }

    public static function blueprintsKey(?string $versionCode): string
    {
        return sprintf('filters:blueprints:%s', self::normalizeVersion($versionCode));
    }

    private static function normalizeVersion(?string $versionCode): string
    {
        return $versionCode === null || $versionCode === '' ? 'default' : strtolower($versionCode);
    }

    private static function trackKey(string $namespace, string $key): void
    {
        $indexKey = self::indexKey($namespace);
        $keys = Cache::get($indexKey, []);

        if (! is_array($keys)) {
            $keys = [];
        }

        if (in_array($key, $keys, true)) {
            return;
        }

        $keys[] = $key;

        Cache::forever($indexKey, $keys);
    }

    private static function indexKey(string $namespace): string
    {
        return sprintf('filters:index:%s', $namespace);
    }
}
