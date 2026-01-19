<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Support\Items\ItemTableConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly ItemTableConfig $itemTableConfig,
    ) {}

    public function index(Request $request): View
    {
        $endpointFilters = $this->normalizeFilterParams($request->input('filter', []));
        $category = $endpointFilters['category'] ?? null;
        $type = $endpointFilters['type'] ?? null;
        $resolvedType = $type ?? $category;

        $initialTableData = $this->apiJsonRequest->request(route('items.index', [], false), $request);
        $filterPayload = $this->apiJsonRequest->request(route('items.filters', [], false), $request);

        $filterOptions = Arr::get($filterPayload, 'filters', []);

        $tableConfig = $this->itemTableConfig->build($resolvedType);

        return view('items.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $filterOptions,
            'initialFilters' => $this->buildInitialFilters($endpointFilters),
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'endpointRouteName' => 'items.index',
            'endpointFilters' => $endpointFilters,
        ]);
    }

    public function show(Request $request, string $item): View
    {
        $include = array_filter(array_map('trim', explode(',', (string) $request->query('include', ''))));
        $include = array_values(array_unique(array_merge($include, ['related_items'])));

        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', implode(',', $include));

        $payload = $this->apiJsonRequest->request(route('items.show', ['identifier' => $item], false), $apiRequest);
        $itemData = Arr::get($payload, 'data', []);

        if ($itemData === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('items.show', [
            'item' => $itemData,
            'itemMeta' => Arr::get($payload, 'meta', []),
            'pageTitle' => Arr::get($itemData, 'name', 'Item'),
        ]);
    }

    private function buildInitialFilters(array $filters): array
    {
        if ($filters === []) {
            return [];
        }

        $initialFilters = [];

        foreach ($filters as $field => $value) {
            if ($field === 'category') {
                continue;
            }

            $initialFilters[] = [
                'field' => $field,
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
}
