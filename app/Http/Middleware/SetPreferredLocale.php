<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SetPreferredLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $language = $request->getPreferredLanguage(['de', 'en']);

        if (Auth::user()) {
            $language = optional(Auth::user()->settings)->language ?? $language;
        }

        app()->setLocale($language);

        return $next($request);
    }
}
