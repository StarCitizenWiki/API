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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $rpm = DB::table('users')->where('api_token', $request->get('user', null))->value('requests_per_minute');

        if ($rpm === null) {
            $rpm = THROTTLE_GUEST_REQUESTS;
        }

        return parent::handle($request, $next, $rpm, THROTTLE_PERIOD);
    }
}
