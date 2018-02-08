<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Class ThrottleApi
 * Throttles Request based on User
 *
 * @package App\Http\Middleware
 */
class ThrottleApi extends ThrottleRequests
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request      Request
     * @param \Closure                 $next         Next
     * @param int                      $maxAttempts  Max Attempts
     * @param int                      $decayMinutes Block Duration
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $user = $request->user();

        if (!is_null($user) && $user->isUnthrottled()) {
            return $next($request);
        }

        return parent::handle($request, $next, config('api.throttle.guest_requests'), config('api.throttle.period'));
    }
}
