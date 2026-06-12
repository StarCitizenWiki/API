<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen\Starmap;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Models\StarCitizen\Starmap\CelestialObject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

#[CacheTag('starmap')]
class CelestialObjectController extends Controller
{
    public function index(Request $request): View
    {
        return view('starmap.celestial-objects.index', [
            'initialHeaderFilter' => [],
        ]);
    }

    public function show(string $code): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }

    public function legacyRedirect(Request $request, string $id): RedirectResponse
    {
        $object = CelestialObject::where('cig_id', $id)->firstOrFail();
        $target = route('web.starmap.celestial-objects.show', ['code' => $object->code]);
        $queryString = $request->getQueryString();

        if ($queryString !== null && $queryString !== '') {
            $target .= '?'.$queryString;
        }

        return redirect($target, 301);
    }
}
