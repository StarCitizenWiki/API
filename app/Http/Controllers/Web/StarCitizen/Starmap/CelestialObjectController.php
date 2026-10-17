<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen\Starmap;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Support\Redirects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

#[CacheTag('starmap')]
class CelestialObjectController extends Controller
{
    public function index(Request $request): View
    {
        return view('starmap.celestial-objects.index');
    }

    public function show(string $code): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }

    public function legacyRedirect(Request $request, string $id): RedirectResponse
    {
        return Redirects::legacyCigId($request, $id, CelestialObject::class, 'web.starmap.celestial-objects.show');
    }
}
