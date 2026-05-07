<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Attributes\CacheTag;
use Closure;
use Illuminate\Http\Request;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

readonly class AddCloudflareCacheTags
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodSafe()) {
            return $response;
        }

        $controller = $request->route()?->getController();

        if ($controller === null) {
            return $response;
        }

        $reflection = new ReflectionClass($controller);
        $attributes = $reflection->getAttributes(CacheTag::class);

        if ($attributes === []) {
            return $response;
        }

        $cacheTag = $attributes[0]->newInstance();
        $scope = $request->is('api/*') ? 'api' : 'web';

        $tags = [$scope, ...array_map(
            static fn (string $tag): string => "{$scope}-{$tag}",
            $cacheTag->tags,
        )];

        $response->headers->set('Cache-Tag', implode(', ', $tags));

        return $response;
    }
}
