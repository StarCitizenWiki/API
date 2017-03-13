<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\DB;

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
            $user = DB::table('users')->where('api_token', $key)->first();
            if (!is_null($user)) {
                $user = User::find($user->id);
                $user->api_token_last_used = date('Y-m-d H:i:s');
                $user->save();
            }
        }

        return $next($request);
    }
}
