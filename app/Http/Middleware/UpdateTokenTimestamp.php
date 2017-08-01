<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Models\APIRequests;
use App\Models\User;
use App\Traits\ProfilesMethodsTrait;
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
    use ProfilesMethodsTrait;

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
        $this->startProfiling(__FUNCTION__);

        $key = $request->get(AUTH_KEY_FIELD_NAME, null);

        if (!is_null($key)) {
            try {
                $user = User::where('api_token', $key)->firstOrFail();
                $user->api_token_last_used = date('Y-m-d H:i:s');
                $user->save();

                app('Log')::info("Updated Token Last Used Timestamp for User: {$user->id}");
                APIRequests::create(
                    [
                        'user_id'     => $user->id,
                        'request_uri' => $request->path(),
                    ]
                );
            } catch (ModelNotFoundException $e) {
                app('Log')::notice("Provided Api Key: {$key} has no associated user");
            }
        }

        $this->stopProfiling(__FUNCTION__);

        return $next($request);
    }
}
