<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;

/**
 * Class UpdateTokenTimestamp
 * If a API Key is present update the last_used field of the User model
 *
 * @package App\Http\Middleware
 */
class UpdateTokenTimestamp
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request
     * @param \Closure                 $next    Next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!is_null($user)) {
            $user->api_token_last_used = date('Y-m-d H:i:s');
            $user->save();

            app('Log')::info("Updated Token Last Used Timestamp for User: {$user->name} ({$user->id})");
        }

        return $next($request);
    }
}
