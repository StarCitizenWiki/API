<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;

/**
 * Aborts the current request if the user is blacklisted
 */
class CheckUserState
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var \App\Models\Account\User\User $user */
        $user = $request->user();

        if (!is_null($user) && $user->isBlocked()) {
            app('Log')::notice(
                'Request from blacklisted User',
                [
                    'user_id' => $user->id,
                    'request_url' => $request->getUri(),
                ]
            );

            abort(403, __('Benutzer ist gesperrt'));
        }

        return $next($request);
    }
}
