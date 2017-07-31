<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Exceptions\UserBlacklistedException;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Class ThrottleAPI
 * Throttles Request based on User
 *
 * @package App\Http\Middleware
 */
class ThrottleAPI extends ThrottleRequests
{
    use ProfilesMethodsTrait;

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
        $this->startProfiling(__FUNCTION__);

        $this->addTrace(__FUNCTION__, 'Getting User From Request', __LINE__);
        $user = User::where('api_token', $request->get(AUTH_KEY_FIELD_NAME, null))->first();

        if (!is_null($user)) {
            if ($user->whitelisted) {
                $this->addTrace(__FUNCTION__, 'User is Whitelisted, no Throttling', __LINE__);
                $this->stopProfiling(__FUNCTION__);

                return $next($request);
            }
        } elseif (!is_null($request->get(AUTH_KEY_FIELD_NAME))) {
            app('Log')::notice("No User for key: {$request->get(AUTH_KEY_FIELD_NAME)} found");
        }

        try {
            $rpm = $this->determineRequestsPerMinute($user);
            $this->addTrace(__FUNCTION__, "Got RPM: {$rpm} for Request");
        } catch (UserBlacklistedException $e) {
            app('Log')::notice('Request from blacklisted User', [
                'user_id' => $user->id,
                'request_url' => $request->getUri(),
            ]);

            $this->stopProfiling(__FUNCTION__);

            abort(403, 'API Key blacklisted');
        }

        $this->stopProfiling(__FUNCTION__);

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
    private function determineRequestsPerMinute($user)
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
