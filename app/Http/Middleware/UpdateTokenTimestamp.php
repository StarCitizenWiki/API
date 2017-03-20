<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateTokenTimestamp
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
        $key = $request->get(AUTH_KEY_FIELD_NAME, null);

        if (!is_null($key)) {
            try {
                $user = User::where('api_token', $key)->firstOrFail();
                $user->api_token_last_used = date('Y-m-d H:i:s');
                $user->save();
            } catch (ModelNotFoundException $e) {
                Log::info('Provided Api Key has no associated user', [
                    'api_token' => $key
                ]);
            }
        }

        return $next($request);
    }
}
