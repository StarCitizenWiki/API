<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Support\Resources\CommodityTableConfig;
use App\Support\Seo\CommodityShowSeoData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class CommodityController extends Controller
{
    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly CommodityTableConfig $commodityTableConfig,
        private readonly CommodityShowSeoData $commodityShowSeoData,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));
        $apiRequest = $this->prepareApiRequest($request, $endpointFilters);
        $tableConfig = $this->commodityTableConfig->build();

        $initialTableData = $this->apiJsonRequest->request(route('commodities.index', [], false), $apiRequest);
        $filterPayload = $this->apiJsonRequest->request(route('commodities.filters', [], false), $apiRequest);

        return view('commodities.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => Arr::get($filterPayload, 'filters', []),
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
            'pageTitle' => Arr::get($commodityData, 'name', 'Commodity'),
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

    /**
     * @return array<string, string>
     */
    private function normalizeFilterParams(mixed $filters): array
    {
        if (! is_array($filters) || $filters === []) {
            return [];
        }

        $normalized = [];

        foreach ($filters as $field => $value) {
            if (! is_string($field) || $field === '') {
                continue;
            }

            $normalizedValue = $this->normalizeFilterValue($value);

            if ($normalizedValue === null) {
                continue;
            }

            $normalized[$field] = $normalizedValue;
        }

        return $normalized;
    }

    private function normalizeFilterValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $values = array_map(static fn (mixed $entry): string => trim((string) $entry), $value);
            $values = array_values(array_filter($values, static fn (string $entry): bool => $entry !== ''));

            if ($values === []) {
                return null;
            }

            return implode(',', $values);
        }

        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
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
