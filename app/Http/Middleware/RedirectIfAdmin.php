<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Class RedirectIfAdmin
 *
 * @package App\Http\Middleware
 */
class RedirectIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if (Auth::guard($guard)->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
