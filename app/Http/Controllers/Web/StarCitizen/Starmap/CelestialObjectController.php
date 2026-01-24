<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Services\ApiJsonRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CelestialObjectController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $initialTableData = $this->apiJsonRequest->request(
            route('celestial-objects.index', ['include' => 'starsystem'], false),
            $request
        );

        return view('starmap.celestial-objects.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => [],
        ]);
    }

    public function show(string $code): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }

    public function legacyRedirect(string $id): RedirectResponse
    {
        $object = CelestialObject::where('cig_id', $id)->firstOrFail();

        return redirect(route('web.starmap.celestial-objects.show', ['code' => $object->code]), 301);
    }
}
