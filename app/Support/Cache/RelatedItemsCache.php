<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

final class RelatedItemsCache
{
    private const NAMESPACE = 'related_items';

    private const TTL_HOURS = 24;

    public static function rememberVariantGroup(?string $versionCode, int $itemId, Closure $callback): array
    {
        $key = self::variantGroupKey($versionCode, $itemId);

        return Cache::remember(
            $key,
            now()->addHours(self::TTL_HOURS),
            $callback
        );
    }

    public static function rememberSetItems(?string $versionCode, string $className, Closure $callback): array
    {
        $key = self::setItemsKey($versionCode, $className);

        return Cache::remember(
            $key,
            now()->addHours(self::TTL_HOURS),
            $callback
        );
    }

    public static function rememberTagGroup(
        ?string $versionCode,
        string $series,
        string $set,
        ?string $type,
        Closure $callback
    ): array {
        $key = self::tagGroupKey($versionCode, $series, $set, $type);

        return Cache::remember(
            $key,
            now()->addHours(self::TTL_HOURS),
            $callback
        );
    }

    public static function flush(string $versionCode): void
    {
        $pattern = self::NAMESPACE.':'.self::normalizeVersion($versionCode).':*';

        $store = Cache::getStore();

        if ($store instanceof \Illuminate\Cache\RedisStore) {
            $redis = $store->connection();
            $keys = $redis->keys($pattern);

            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }

    private static function variantGroupKey(?string $versionCode, int $itemId): string
    {
        return sprintf(
            '%s:variant_group:%s:%d',
            self::NAMESPACE,
            self::normalizeVersion($versionCode),
            $itemId
        );
    }

    private static function setItemsKey(?string $versionCode, string $className): string
    {
        return sprintf(
            '%s:set_items:%s:%s',
            self::NAMESPACE,
            self::normalizeVersion($versionCode),
            md5($className)
        );
    }

    private static function tagGroupKey(?string $versionCode, string $series, string $set, ?string $type): string
    {
        return sprintf(
            '%s:tag_group:%s:%s:%s:%s',
            self::NAMESPACE,
            self::normalizeVersion($versionCode),
            md5($series),
            md5($set),
            $type ?? 'null'
        );
    }

    private static function normalizeVersion(?string $versionCode): string
    {
        return $versionCode === null || $versionCode === '' ? 'default' : strtolower($versionCode);
    }
}
