<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Redirects
{
    public static function withQueryString(Request $request, string $target, int $status = Response::HTTP_MOVED_PERMANENTLY): RedirectResponse
    {
        $queryString = $request->getQueryString();

        if ($queryString !== null && $queryString !== '') {
            $target .= '?'.$queryString;
        }

        return redirect($target, $status);
    }

    /**
     * Redirect a legacy cig_id based route to its code based show route
     *
     * @param  class-string<Model>  $model
     */
    public static function legacyCigId(Request $request, string $id, string $model, string $routeName): RedirectResponse
    {
        $code = $model::query()->where('cig_id', $id)->firstOrFail()->code;

        return self::withQueryString($request, route($routeName, ['code' => $code]));
    }
}
