<?php declare(strict_types = 1);

namespace App\Http\Middleware\Web\User;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class RedirectIfAuthenticated
 */
class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Request       $request
     * @param Closure       $next
     * @param string | null $guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return redirect()->intended(route('web.user.account.index'));
        }

        return $next($request);
    }
}
