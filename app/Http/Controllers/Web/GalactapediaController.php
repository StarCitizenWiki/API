<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class GalactapediaController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $initialTableData = $this->apiJsonRequest->request(route('galactapedia.index', [], false), $request);
        $filterPayload = $this->apiJsonRequest->request(route('galactapedia.filters', [], false), $request);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('galactapedia.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
        ]);
    }

    public function show(string $article): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }
}
