<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class CheckIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
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

        Log::notice('Unauthenticated User tried to access Admin area', [
            'user_id' => Auth::id()
        ]);

        return abort(403, 'No Permission');
    }
}
