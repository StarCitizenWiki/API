<?php

namespace App\Http\Middleware;

use App\Traits\ProfilesMethodsTrait;
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
    use ProfilesMethodsTrait;

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
        $this->startProfiling(__FUNCTION__);

        if (Auth::check()) {
            $user = Auth::user();

            if (in_array($user->id, AUTH_ADMIN_IDS)) {
                $this->addTrace(__FUNCTION__, "User with ID: {$user->id} is Admin", __LINE__);
                $this->stopProfiling(__FUNCTION__);

                return $next($request);
            }
        }

        app('Log')::notice("Unauthenticated User with ID: ".Auth::id()." tried to access Admin area");

        $this->stopProfiling(__FUNCTION__);

        return abort(403, 'No Permission');
    }
}
