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

    public static function rememberForever(string $namespace, string $key, Closure $resolver): mixed
    {
        self::trackKey($namespace, $key);

        if (app()->environment('production')) {
            return Cache::rememberForever($key, $resolver);
        }

        return $resolver();
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

    public static function itemsFiltersKey(?string $versionCode, string $category, string $filtersHash): string
    {
        return sprintf(
            'filters:items:%s:%s:%s',
            self::normalizeVersion($versionCode),
            $category,
            $filtersHash
        );
    }

    public static function vehiclesKey(?string $versionCode, string $vehicleType): string
    {
        return sprintf('filters:vehicles:%s:%s', self::normalizeVersion($versionCode), $vehicleType);
    }

    public static function starmapLocationsFiltersKey(?string $versionCode, string $filtersHash): string
    {
        return sprintf(
            'filters:starmap-locations:%s:%s',
            self::normalizeVersion($versionCode),
            $filtersHash
        );
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

    public static function shipMatrixKey(): string
    {
        return 'filters:shipmatrix';
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
