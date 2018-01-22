<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Models\ApiRequests;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $key = $request->header('Authorization', null);

        if (is_null($key)) {
            $key = $request->query->get('Authorization', null);
        }

        if (!is_null($key)) {
            try {
                $user = User::where('api_token', $key)->firstOrFail();
                $user->api_token_last_used = date('Y-m-d H:i:s');
                $user->save();

                app('Log')::info("Updated Token Last Used Timestamp for User: {$user->id}");
                ApiRequests::create(
                    [
                        'user_id'     => $user->id,
                        'request_uri' => $request->path(),
                    ]
                );
            } catch (ModelNotFoundException $e) {
                app('Log')::notice("Provided Api Key: {$key} has no associated user");
            }
        }

        return $next($request);
    }
}
