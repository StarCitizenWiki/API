<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;

class ThrottleAPI extends ThrottleRequests
{

    public function __construct(RateLimiter $limiter)
    {
        parent::__construct($limiter);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param int $maxAttempts
     * @param int $decayMinutes
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $user = DB::table('users')->where('api_token', $request->get('key', null))->first();

        // Whitelist hat kein Throttling
        if (!is_null($user) && $user->whitelisted) {
            return $next($request);
        }

        $rpm = $this->_determinteRequestsPerMinute($user);

        return parent::handle($request, $next, $rpm, THROTTLE_PERIOD);
    }

    private function _determinteRequestsPerMinute($user)
    {
        if (is_null($user)) {
            return THROTTLE_GUEST_REQUESTS;
        }

        if ($user->blacklisted) {
            return 0;
        }

        return $user->requests_per_minute;
    }
}
