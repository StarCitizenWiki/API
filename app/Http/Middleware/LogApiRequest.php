<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Models\ApiRequests;
use Closure;

/**
 * If a user is present in the request, the Api request will be logged to DB
 */
class LogApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!is_null($user)) {
            ApiRequests::create(
                [
                    'user_id' => $user->id,
                    'request_uri' => $request->path(),
                ]
            );

            app('Log')::info("Logged Api Request for User: {$user->name} ({$user->id})");
        }

        return $next($request);
    }
}
