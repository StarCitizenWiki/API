<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 29.08.2017
 * Time: 11:01
 */

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Class CachesResponsesTrait
 */
trait CachesResponseTrait
{
    /**
     * @param null $key
     *
     * @return bool
     */
    protected function isCached($key = null): bool
    {
        if (is_null($key)) {
            $key = get_cache_key_for_current_request();
        }

        return Cache::has($key);
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    protected function getCachedResponse($key = null)
    {
        if (is_null($key)) {
            $key = get_cache_key_for_current_request();
        }

        return Cache::get($key);
    }

    /**
     * Returns a JsonResponse and caches it
     *
     * @param mixed $data
     * @param null  $key
     * @param int   $cacheTime
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($data, $key = null, $cacheTime = null): JsonResponse
    {
        if (is_null($key)) {
            $key = get_cache_key_for_current_request();
        }

        if (is_null($cacheTime)) {
            $cacheTime = config('cache.duration');
        }

        return Cache::remember(
            $key,
            $cacheTime,
            function () use ($data) {
                return response()->json(
                    $data,
                    200,
                    [],
                    JSON_PRETTY_PRINT
                );
            }
        );
    }
}
