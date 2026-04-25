<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Http\Controllers\Controller;
use App\Models\Game\Blueprint;
use App\Services\ApiJsonRequest;
use App\Support\Blueprints\BlueprintShowViewData;
use App\Support\Blueprints\BlueprintTableConfig;
use App\Support\Seo\BlueprintIndexSeoData;
use App\Support\Seo\BlueprintShowSeoData;
use App\Traits\NormalizesFilterParams;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class BlueprintController extends Controller
{
    use NormalizesFilterParams;

    private const int SEARCH_RESULTS_PAGE_SIZE = 5;

    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly BlueprintShowViewData $blueprintShowViewData,
        private readonly BlueprintShowSeoData $blueprintShowSeoData,
        private readonly BlueprintTableConfig $blueprintTableConfig,
        private readonly BlueprintIndexSeoData $blueprintIndexSeoData,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));

        $tableConfig = $this->blueprintTableConfig->build();
        $initialTableData = $this->apiJsonRequest->request(
            route('blueprints.index', $this->buildVersionedRouteParameters($request, [
                'page' => ['size' => $tableConfig['pageSize']],
            ]), false),
            $request,
        );
        $filterPayload = $this->apiJsonRequest->request(
            route('blueprints.filters', $this->buildVersionedRouteParameters($request), false),
            $request,
        );

        return view('blueprints.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => Arr::get($filterPayload, 'filters', []),
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'pageSize' => $tableConfig['pageSize'],
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'seo' => $this->blueprintIndexSeoData->build([
                'pageTitle' => $tableConfig['title'],
                'total' => Arr::get($initialTableData, 'meta.total', 0),
                'output_type' => $endpointFilters['output.type'] ?? null,
                'output_class' => $endpointFilters['output.class'] ?? null,
            ], $request),
        ]);
    }

    public function app(Request $request, ?Blueprint $blueprint = null): View
    {
        if ($blueprint === null) {
            return view('blueprints.show', [
                ...$this->blueprintShowViewData->build(
                    mode: 'empty',
                    blueprint: [],
                    search: $this->buildSearchState($request),
                    pageTitle: 'Search Blueprints',
                ),
                'seo' => $this->blueprintShowSeoData->build([], $request, isEmptyMode: true),
            ]);
        }

        $payload = $this->apiJsonRequest->request(
            route('blueprints.show', $this->buildVersionedRouteParameters($request, [
                'blueprint' => $blueprint,
            ]), false),
            $request,
        );
        $blueprintData = Arr::get($payload, 'data', []);

        if ($blueprintData === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('blueprints.show', [
            ...$this->blueprintShowViewData->build(
                mode: 'detail',
                blueprint: $blueprintData,
                search: $this->buildSearchState($request),
                pageTitle: Arr::get($blueprintData, 'output_name')
                    ?? Arr::get($blueprintData, 'output.name')
                    ?? 'Blueprint',
            ),
            'seo' => $this->blueprintShowSeoData->build($blueprintData, $request),
        ]);
    }

    /**
     * @return array{
     *     api_endpoint: string,
     *     resource_types_endpoint: string,
     *     filters: array<string, string>,
     *     query: string,
     *     results: array<int, mixed>,
     *     result_count: int
     * }
     */
    private function buildSearchState(Request $request): array
    {
        $apiEndpoint = route('blueprints.index', $this->buildVersionedRouteParameters($request, [
            'page' => ['size' => self::SEARCH_RESULTS_PAGE_SIZE],
        ]), false);
        $resourceTypesEndpoint = route('commodities.index', $this->buildVersionedRouteParameters($request, [
            'filter' => ['used' => 'true'],
        ]), false);
        $filters = $this->normalizeFilterParams($request->input('filter', []));
        $payload = $filters === []
            ? []
            : $this->apiJsonRequest->request($apiEndpoint, $request);
        $results = Arr::get($payload, 'data', []);

        return [
            'api_endpoint' => $apiEndpoint,
            'resource_types_endpoint' => $resourceTypesEndpoint,
            'filters' => $filters,
            'query' => $filters['query'] ?? '',
            'results' => is_array($results) ? $results : [],
            'result_count' => max(
                0,
                (int) Arr::get($payload, 'meta.total', is_array($results) ? count($results) : 0),
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    private function buildVersionedRouteParameters(Request $request, array $parameters = []): array
    {
        return array_filter([
            ...$parameters,
            'version' => $this->resolveVersionCode($request),
        ], static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
    }

    private function resolveVersionCode(Request $request): ?string
    {
        $requestedVersionCode = $this->normalizeFilterValue($request->query('version'));

        if ($requestedVersionCode !== null) {
            return $requestedVersionCode;
        }

        if (! $request->hasSession()) {
            return null;
        }

        return $this->normalizeFilterValue($request->session()->get('game_version_code'));
    }
}
