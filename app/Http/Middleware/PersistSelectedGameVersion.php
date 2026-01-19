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
            $requestedCode = $this->normalizeCode($request->query('version'));
            $sessionCode = $this->normalizeCode($request->session()->get('game_version_code'));
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
                    }
                }
            }
        }

        return $next($request);
    }

    protected function resolveVersionCode(string $code): ?string
    {
        return GameVersion::query()
            ->whereRaw('LOWER(code) = ?', [strtolower($code)])
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

    protected function normalizeCode(mixed $code): ?string
    {
        if (! is_string($code)) {
            return null;
        }

        $code = trim($code);

        return $code === '' ? null : $code;
    }
}
