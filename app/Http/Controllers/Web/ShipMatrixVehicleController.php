<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ShipMatrixVehicleController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $initialTableData = $this->apiJsonRequest->request(route('shipmatrix.vehicles.index', [], false), $request);
        $filterPayload = $this->apiJsonRequest->request(route('shipmatrix.vehicles.filters', [], false), $request);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('ship-matrix.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
        ]);
    }

    public function show(string $vehicle): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }
}
