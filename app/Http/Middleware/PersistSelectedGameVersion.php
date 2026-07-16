<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Game\GameVersion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PersistSelectedGameVersion
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasSession()) {
            $rawRequested = $request->query('version');
            $requestedCode = is_string($rawRequested) && trim($rawRequested) !== '' ? trim($rawRequested) : null;
            $rawSession = $request->session()->get('game_version_code');
            $sessionCode = is_string($rawSession) && trim($rawSession) !== '' ? trim($rawSession) : null;
            $defaultCode = null;

            if ($requestedCode !== null) {
                $resolvedCode = $this->resolveVersionCode($requestedCode);
                $defaultCode = $this->resolveDefaultVersionCode();

                if ($resolvedCode !== null && $resolvedCode !== $defaultCode) {
                    $request->session()->put('game_version_code', $resolvedCode);
                } else {
                    $request->session()->forget('game_version_code');
                }
            } elseif ($sessionCode !== null) {
                $resolvedSessionCode = $this->resolveVersionCode($sessionCode);

                if ($resolvedSessionCode === null) {
                    $request->session()->forget('game_version_code');
                } else {
                    $defaultCode = $defaultCode ?? $this->resolveDefaultVersionCode();

                    if ($resolvedSessionCode === $defaultCode) {
                        $request->session()->forget('game_version_code');
                    } elseif ($requestedCode === null && $request->route('version') === null) {
                        // No explicit version in URL but session has a valid non-default version.
                        // Inject it into the query so internal API sub-requests pick it up.
                        //
                        // Skip path-versioned routes (e.g. /changelog/{version})
                        $request->query->set('version', $resolvedSessionCode);
                        $request->server->set('QUERY_STRING', $request->getQueryString());
                    }
                }
            }
        }

        return $next($request);
    }

    protected function resolveVersionCode(string $code): ?string
    {
        return GameVersion::query()
            ->where('code', strtoupper($code))
            ->value('code');
    }

    protected function resolveDefaultVersionCode(): ?string
    {
        return GameVersion::query()
            ->orderByDesc('is_default')
            ->orderByDesc('released_at')
            ->orderBy('code')
            ->value('code');
    }
}
