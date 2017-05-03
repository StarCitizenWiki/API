<?php

namespace App\Http\Middleware;

use App\Models\APIRequests;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;

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
        $key = $request->get(AUTH_KEY_FIELD_NAME, null);

        if (!is_null($key)) {
            try {
                $user = User::where('api_token', $key)->firstOrFail();
                $user->api_token_last_used = date('Y-m-d H:i:s');
                $user->save();
                App::make('Log')->debug('Updated Token Last Used', [
                    'user_id' => $user->id,
                ]);
                APIRequests::create([
                    'user_id' => $user->id,
                    'request_uri' => $request->path(),
                ]);
            } catch (ModelNotFoundException $e) {
                App::make('Log')->info('Provided Api Key has no associated user', [
                    'api_token' => $key,
                ]);
            }
        }

        return $next($request);
    }
}
