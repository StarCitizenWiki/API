<?php

declare(strict_types=1);

namespace App\Http\Middleware\Api\Game;

use App\Models\Game\GameVersion;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ResolveGameVersion
{
    /**
     * Resolve the 'version' query parameter once and cache it in request attributes.
     * Unknown values throw 422 instead of 404.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $versionCode = $request->query('version');

        if ($versionCode !== null && ! is_string($versionCode)) {
            throw ValidationException::withMessages([
                'version' => 'The version parameter must be a string. See GET /api/game-versions for valid version codes.',
            ]);
        }

        $gameVersion = $versionCode !== null
            ? GameVersion::findByCode($versionCode) ?? throw $this->unknownVersion($versionCode)
            : GameVersion::resolveRequestedOrDefault(null);

        $request->attributes->set('game_version', $gameVersion);
        $request->attributes->set('game_version_code', $versionCode);

        return $next($request);
    }

    private function unknownVersion(string $code): ValidationException
    {
        $default = GameVersion::query()->where('is_default', true)->value('code');

        $message = sprintf('Unknown game version "%s".', $code);

        if (is_string($default)) {
            $message .= sprintf(' Current default: "%s".', $default);
        }

        $message .= ' See GET /api/game-versions for valid version codes.';

        return ValidationException::withMessages(['version' => $message]);
    }
}
