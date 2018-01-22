<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Exceptions\AccountDisabledExceptionAbstract;
use App\Models\User;
use Closure;
use Illuminate\Cache\RateLimiter;
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
     * ThrottleApi constructor.
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
        $key = $request->header('Authorization', null);

        if (is_null($key)) {
            $key = $request->query->get('Authorization', null);
        }

        $user = User::where('api_token', $key)->first();

        if (!is_null($user)) {
            if ($user->isWhitelisted()) {
                return $next($request);
            }
        } elseif (!is_null($key)) {
            app('Log')::notice("No User for key: {$key} found");
        }

        try {
            $rpm = $this->determineRequestsPerMinute($user);
        } catch (AccountDisabledExceptionAbstract $e) {
            app('Log')::notice(
                'Request from blacklisted User',
                [
                    'user_id'     => $user->id,
                    'request_url' => $request->getUri(),
                ]
            );

            abort(403, __('Benutzer ist gesperrt'));
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
     * @throws AccountDisabledExceptionAbstract
     */
    private function determineRequestsPerMinute($user)
    {
        if (is_null($user)) {
            return THROTTLE_GUEST_REQUESTS;
        }

        if ($user->isBlacklisted()) {
            throw new AccountDisabledExceptionAbstract();
        }

        return $user->requests_per_minute;
    }
}
