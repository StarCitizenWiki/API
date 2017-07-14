<?php

namespace App\Http\Middleware;

use App\Facades\Log;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

/**
 * Class CheckIfAdmin
 * Checks if a UserID is in the defined AdminArray
 *
 * @package App\Http\Middleware
 */
class CheckIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request
     * @param \Closure                 $next    Next Function
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (App::isLocal()) {
            return $next($request);
        }

        if (Auth::check()) {
            $user = Auth::user();

            if (in_array($user->id, AUTH_ADMIN_IDS)) {
                return $next($request);
            }
        }

        Log::info('Unauthenticated User tried to access Admin area', [
            'user_id' => Auth::id(),
        ]);

        return abort(403, 'No Permission');
    }
}
