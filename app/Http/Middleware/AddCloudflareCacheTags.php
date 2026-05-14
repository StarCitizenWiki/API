<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Attributes\CacheTag;
use App\Models\Game\GameVersion;
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

        $versionTag = $this->resolveVersionTag($request, $scope);

        if ($versionTag !== null) {
            $tags[] = $versionTag;
        }

        $response->headers->set('Cache-Tag', implode(', ', $tags));

        return $response;
    }

    /**
     * Resolve a version-based cache tag from the resolved GameVersion.
     *
     * Returns "<scope>-default" when the resolved version is the default,
     * otherwise extracts major.minor.patch (e.g. "4.8.0" from "4.8.0-LIVE.123")
     * and returns "<scope>-v4.8.0".
     */
    private function resolveVersionTag(Request $request, string $scope): ?string
    {
        $gameVersion = $request->attributes->get('game_version');

        if (! ($gameVersion instanceof GameVersion)) {
            return null;
        }

        if ($gameVersion->is_default) {
            return "{$scope}-default";
        }

        $code = $gameVersion->code;

        if ($code === null || ! preg_match('/^(\d+\.\d+\.\d+)/', $code, $matches)) {
            return null;
        }

        return "{$scope}-v{$matches[1]}";
    }
}
