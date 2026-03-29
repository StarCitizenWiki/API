<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Http\Controllers\Controller;
use App\Models\Game\Blueprint;
use App\Services\ApiJsonRequest;
use App\Support\Blueprints\BlueprintShowViewData;
use App\Support\Blueprints\BlueprintTableConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class BlueprintController extends Controller
{
    private const SEARCH_RESULTS_PAGE_SIZE = 5;

    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly BlueprintShowViewData $blueprintShowViewData,
        private readonly BlueprintTableConfig $blueprintTableConfig,
    ) {}

    public function index(Request $request): View
    {
        $tableConfig = $this->blueprintTableConfig->build();
        $initialTableData = $this->apiJsonRequest->request(
            route('blueprints.index', $this->buildVersionedRouteParameters($request, [
                'page' => ['size' => $tableConfig['pageSize']],
            ]), false),
            $request,
        );

        return view('blueprints.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => [],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'pageSize' => $tableConfig['pageSize'],
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
        ]);
    }

    public function app(Request $request, ?Blueprint $blueprint = null): View
    {
        if ($blueprint === null) {
            return view('blueprints.show', $this->blueprintShowViewData->build(
                mode: 'empty',
                blueprint: [],
                search: $this->buildSearchState($request),
                pageTitle: 'Search Blueprints',
            ));
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

        return view('blueprints.show', $this->blueprintShowViewData->build(
            mode: 'detail',
            blueprint: $blueprintData,
            search: $this->buildSearchState($request),
            pageTitle: Arr::get($blueprintData, 'output_name')
                ?? Arr::get($blueprintData, 'output.name')
                ?? 'Blueprint',
        ));
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
        $resourceTypesEndpoint = route('resource-types.index', $this->buildVersionedRouteParameters($request, [
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
