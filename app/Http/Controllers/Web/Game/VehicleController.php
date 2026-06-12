<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Support\Seo\VehicleIndexSeoData;
use App\Support\Seo\VehicleShowSeoData;
use App\Traits\NormalizesFilterParams;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

#[CacheTag('vehicles')]
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

        return view('vehicles.index', [
            'pageTitle' => 'Vehicles',
            'initialHeaderFilter' => [],
            'initialFilters' => $this->buildInitialFilters($endpointFilters),
            'seo' => $this->vehicleIndexSeoData->build([
                'pageTitle' => 'Vehicles',
                'total' => null,
                'manufacturer' => $endpointFilters['manufacturer'] ?? null,
            ], $request),
        ]);
    }

    public function show(Request $request, string $item): View
    {
        $include = explode(',', (string) $request->query('include', ''))
                |> (static fn ($x) => array_map('trim', $x))
                |> array_filter(...);
        $include = array_merge($include, ['shipMatrixVehicle'])
                |> array_unique(...)
                |> array_values(...);

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
}
