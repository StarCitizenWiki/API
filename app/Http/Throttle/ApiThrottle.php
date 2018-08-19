<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 01.08.2018
 * Time: 21:27
 */

namespace App\Http\Throttle;

use Dingo\Api\Auth\Auth;
use Dingo\Api\Http\RateLimit\Throttle\Throttle;
use Illuminate\Container\Container;

/**
 * Api Throttle based on User or Guest
 */
class ApiThrottle extends Throttle
{
    /**
     * Attempt to match the throttle against a given condition.
     *
     * @param \Illuminate\Container\Container $container
     *
     * @return bool
     */
    public function match(Container $container)
    {
        if (app(Auth::class)->check(true) && app(Auth::class)->user()->isUnthrottled()) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        if (app(Auth::class)->check(true)) {
            // TODO default Period 1 Minute?
            return 1;
        }

        return parent::getExpires();
    }

    /**
     * @return int|mixed
     */
    public function getLimit()
    {
        if (app(Auth::class)->check(true)) {
            return app(Auth::class)->user()->requests_per_minute;
        }

        return parent::getLimit();
    }
}
