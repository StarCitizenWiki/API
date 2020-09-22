<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Account\User\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Aborts the current request if the user is blacklisted
 */
class CheckUserState
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = $request->user();

        if ($user !== null && $user->blocked) {
            app('Log')::notice(
                'Request from blacklisted User',
                [
                    'user_id' => $user->id,
                    'request_url' => $request->getUri(),
                ]
            );

            Auth::logout();

            abort(403, __('Benutzer ist gesperrt'));
        }

        return $next($request);
    }
}
