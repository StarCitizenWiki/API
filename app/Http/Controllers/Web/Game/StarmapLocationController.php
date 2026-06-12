<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Support\Seo\StarmapLocationIndexSeoData;
use App\Support\Seo\StarmapLocationShowSeoData;
use App\Support\Starmap\StarmapLocationShowViewData;
use App\Support\Starmap\StarmapLocationTableConfig;
use App\Traits\NormalizesFilterParams;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

#[CacheTag('starmap')]
class StarmapLocationController extends Controller
{
    use NormalizesFilterParams;

    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly StarmapLocationTableConfig $starmapLocationTableConfig,
        private readonly StarmapLocationShowViewData $starmapLocationShowViewData,
        private readonly StarmapLocationShowSeoData $starmapLocationShowSeoData,
        private readonly StarmapLocationIndexSeoData $starmapLocationIndexSeoData,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));
        $tableConfig = $this->starmapLocationTableConfig->build();

        return view('starmap.locations.index', [
            'initialHeaderFilter' => [],
            'initialFilters' => $this->buildInitialFilters($endpointFilters, $tableConfig['headerFilterOptionsMap']),
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'externalFilters' => $tableConfig['externalFilters'] ?? [],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'seo' => $this->starmapLocationIndexSeoData->build(['pageTitle' => $tableConfig['title']], $request),
        ]);
    }

    public function show(Request $request, string $identifier): View
    {
        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', 'children,resources,missions');

        $payload = $this->apiJsonRequest->request(
            route('locations.show', ['identifier' => $identifier], false),
            $apiRequest
        );
        $locationData = Arr::get($payload, 'data', []);

        if ($locationData === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('starmap.locations.show', [
            'location' => $locationData,
            'viewData' => $this->starmapLocationShowViewData->build(is_array($locationData) ? $locationData : []),
            'pageTitle' => Arr::get($locationData, 'name', 'Starmap Location'),
            'seo' => $this->starmapLocationShowSeoData->build(is_array($locationData) ? $locationData : [], $request),
        ]);
    }

    /**
     * @param  array<string, string>  $filters
     * @param  array<string, string>  $headerFilterOptionsMap
     * @return array<int, array{field: string, value: string}>
     */
    private function buildInitialFilters(array $filters, array $headerFilterOptionsMap): array
    {
        if ($filters === []) {
            return [];
        }

        $initialFilters = [];
        $apiFieldToColumnFieldMap = array_flip($headerFilterOptionsMap);

        foreach ($filters as $field => $value) {
            $initialFilters[] = [
                'field' => $apiFieldToColumnFieldMap[$field] ?? $field,
                'value' => $value,
            ];
        }

        return $initialFilters;
    }
}
