<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Support\Seo\VehicleIndexSeoData;
use App\Support\Seo\VehicleShowSeoData;
use App\Traits\NormalizesFilterParams;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class VehicleController extends Controller
{
    use NormalizesFilterParams;

    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly VehicleShowSeoData $vehicleShowSeoData,
        private readonly VehicleIndexSeoData $vehicleIndexSeoData,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));
        $apiRequest = $this->prepareApiRequest($request, $endpointFilters);

        $initialTableData = $this->apiJsonRequest->request(route('vehicles.index', [], false), $apiRequest);
        $filterPayload = $this->apiJsonRequest->request(route('vehicles.filters', [], false), $apiRequest);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('vehicles.index', [
            'pageTitle' => 'Vehicles',
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
            'initialFilters' => $this->buildInitialFilters($endpointFilters),
            'seo' => $this->vehicleIndexSeoData->build([
                'pageTitle' => 'Vehicles',
                'total' => Arr::get($initialTableData, 'meta.total', 0),
                'manufacturer' => $endpointFilters['manufacturer'] ?? null,
            ], $request),
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

        if (empty($vehicleData)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('vehicles.show', [
            'vehicle' => $vehicleData,
            'vehicleMeta' => Arr::get($payload, 'meta', []),
            'pageTitle' => Arr::get($vehicleData, 'name', 'Vehicle'),
            'seo' => $this->vehicleShowSeoData->build($vehicleData, $request),
        ]);
    }

    private function buildInitialFilters(array $filters): array
    {
        if ($filters === []) {
            return [];
        }

        $initialFilters = [];

        foreach ($filters as $field => $value) {
            $initialFilters[] = [
                'field' => $field,
                'value' => $value,
            ];
        }

        return $initialFilters;
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function prepareApiRequest(Request $request, array $filters): Request
    {
        $apiRequest = $request->duplicate();

        if ($filters === []) {
            $apiRequest->query->remove('filter');

            return $apiRequest;
        }

        $apiRequest->query->set('filter', $filters);

        return $apiRequest;
    }
}
