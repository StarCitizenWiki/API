<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Support\Resources\CommodityTableConfig;
use App\Support\Seo\CommodityShowSeoData;
use App\Traits\NormalizesFilterParams;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

#[CacheTag('commodities')]
class CommodityController extends Controller
{
    use NormalizesFilterParams;

    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly CommodityTableConfig $commodityTableConfig,
        private readonly CommodityShowSeoData $commodityShowSeoData,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));
        $tableConfig = $this->commodityTableConfig->build();

        return view('commodities.index', [
            'initialHeaderFilter' => [],
            'initialFilters' => $this->buildInitialFilters($endpointFilters, $tableConfig['headerFilterOptionsMap']),
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'externalFilters' => $tableConfig['externalFilters'] ?? [],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
        ]);
    }

    public function show(Request $request, string $identifier): View
    {
        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', 'blueprints,items');

        $payload = $this->apiJsonRequest->request(
            route('commodities.show', ['commodity' => $identifier], false),
            $apiRequest
        );
        $commodityData = Arr::get($payload, 'data', []);

        if ($commodityData === []) {
            abort(404);
        }

        return view('commodities.show', [
            'resource' => $commodityData,
            'pageTitle' => Arr::get($commodityData, 'display_name', Arr::get($commodityData, 'name', 'Commodity')),
            'seo' => $this->commodityShowSeoData->build($commodityData, $request),
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
