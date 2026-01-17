<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $initialTableData = $this->apiJsonRequest->request(route('vehicles.index', [], false), $request);
        $filterPayload = $this->apiJsonRequest->request(route('vehicles.filters', [], false), $request);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('vehicles.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
        ]);
    }

    public function show(Request $request, string $item): View
    {
        $include = array_filter(array_map('trim', explode(',', (string) $request->query('include', ''))));
        $include = array_values(array_unique(array_merge($include, ['shipMatrixVehicle'])));

        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', implode(',', $include));

        $payload = $this->apiJsonRequest->request(route('vehicles.show', ['vehicle' => $item], false), $apiRequest);
        $vehicleData = Arr::get($payload, 'data', []);

        if ($vehicleData === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('vehicles.show', [
            'vehicle' => $vehicleData,
            'vehicleMeta' => Arr::get($payload, 'meta', []),
            'pageTitle' => Arr::get($vehicleData, 'name', 'Vehicle'),
        ]);
    }
}
