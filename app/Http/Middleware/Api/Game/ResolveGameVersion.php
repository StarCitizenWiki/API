<?php

declare(strict_types=1);

namespace App\Http\Middleware\Api\Game;

use App\Models\Game\GameVersion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveGameVersion
{
    /**
     * Resolve and cache the requested game version for the entire request lifecycle.
     *
     * Extracts the 'version' query parameter and resolves it to a GameVersion model.
     * If no version is specified, uses the default version.
     * The resolved version is stored in request attributes for efficient access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $versionCode = $request->query('version');

        // Resolve version once and cache in request attributes
        $gameVersion = GameVersion::resolveRequestedOrDefault($versionCode);
        $request->attributes->set('game_version', $gameVersion);
        $request->attributes->set('game_version_code', $versionCode);

        return $next($request);
    }
}
