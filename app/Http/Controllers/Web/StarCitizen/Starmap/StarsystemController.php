<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Models\StarCitizen\Starmap\Starsystem;
use App\Services\ApiJsonRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class StarsystemController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $initialTableData = $this->apiJsonRequest->request(route('starsystems.index', [], false), $request);
        $filterPayload = $this->apiJsonRequest->request(route('starsystems.filters', [], false), $request);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('starmap.systems.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
        ]);
    }

    public function show(string $code): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }

    public function legacyRedirect(Request $request, string $id): RedirectResponse
    {
        $system = Starsystem::where('cig_id', $id)->firstOrFail();
        $target = route('web.starmap.systems.show', ['code' => $system->code]);
        $queryString = $request->getQueryString();

        if ($queryString !== null && $queryString !== '') {
            $target .= '?'.$queryString;
        }

        return redirect($target, 301);
    }
}
