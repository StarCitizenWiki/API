<?php

namespace App\Http\Middleware;

use App\Exceptions\UserBlacklistedException;
use App\Models\User;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Log;

/**
 * Class ThrottleAPI
 * Throttles Request based on User
 *
 * @package App\Http\Middleware
 */
class ThrottleAPI extends ThrottleRequests
{
    /**
     * ThrottleAPI constructor.
     *
     * @param RateLimiter $limiter Limiter
     */
    public function __construct(RateLimiter $limiter)
    {
        parent::__construct($limiter);
    }

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
        $user = User::where('api_token', $request->get('key', null))->first();

        // Whitelist hat kein Throttling
        if (!is_null($user) && $user->whitelisted) {
            return $next($request);
        }

        try {
            $rpm = $this->_determineRequestsPerMinute($user);
        } catch (UserBlacklistedException $e) {
            Log::info(
                'Request from blacklisted User',
                [
                    'user_id' => $user->id,
                    'request_url' => $request->getUri()
                ]
            );
            abort(403, 'API Key blacklisted');
        }

        return parent::handle($request, $next, $rpm, THROTTLE_PERIOD);
    }

    /**
     * Determines the RPM based on the User
     *
     * @param User | null $user User Object
     *
     * @return int
     *
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