<?php

namespace App\Http\Middleware;

use App\Exceptions\UserBlacklistedException;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;

class ThrottleAPI extends ThrottleRequests
{
    /**
     * ThrottleAPI constructor.
     * @param RateLimiter $limiter
     */
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

        try {
            $rpm = $this->_determineRequestsPerMinute($user);
        } catch (UserBlacklistedException $e) {
            abort(403, 'API Key blacklisted');
        }

        return parent::handle($request, $next, $rpm, THROTTLE_PERIOD);
    }

    /**
     * @param $user
     * @return int
     * @throws UserBlacklistedException
     */
    private function _determineRequestsPerMinute($user)
    {
        if (is_null($user)) {
            return THROTTLE_GUEST_REQUESTS;
        }

        if ($user->blacklisted) {
            throw new UserBlacklistedException();
        }

        return $user->requests_per_minute;
    }
}
