<?php

namespace App\Http\Middleware;

use Closure;
use Composer\DependencyResolver\Request;
use Illuminate\Support\Facades\App;
use PiwikTracker;

class PiwikTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * Local nicht tracken
         */
        if (App::environment('production')) {
            /** @var PiwikTracker $piwikClient */
            $piwikClient = new PiwikTracker(PIWIK_SITE_ID);
            $piwikClient::$URL = PIWIK_URL;

            $piwikClient->setUrl($request->fullUrl());
            $piwikClient->setGenerationTime(microtime(true) - LARAVEL_START);
            $piwikClient->setUserId($request->get(AUTH_KEY_FIELD_NAME, false));
            $piwikClient->doTrackPageView($request->getRequestUri());
        }

        return $next($request);
    }
}
