<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Support\Items\ItemTableConfig;
use App\Support\Seo\ItemIndexSeoData;
use App\Support\Seo\ItemShowSeoData;
use App\Traits\NormalizesFilterParams;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

#[CacheTag('items')]
class ItemController extends Controller
{
    use NormalizesFilterParams;

    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly ItemTableConfig $itemTableConfig,
        private readonly ItemShowSeoData $itemShowSeoData,
        private readonly ItemIndexSeoData $itemIndexSeoData,
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

        $tableConfig['externalFilters'] = array_merge($tableConfig['externalFilters'] ?? [], [
            ['title' => 'Include Irrelevant', 'field' => 'include_irrelevant', 'options' => [
                ['value' => '', 'label' => 'Default'],
                ['value' => 'true', 'label' => 'Yes'],
            ]],
        ]);

        $total = Arr::get($initialTableData, 'meta.total');
        $manufacturer = $endpointFilters['manufacturer'] ?? null;

        return view('items.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $filterOptions,
            'initialFilters' => $this->buildInitialFilters($endpointFilters),
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'externalFilters' => $tableConfig['externalFilters'] ?? [],
            'endpointRouteName' => 'items.index',
            'endpointFilters' => $endpointFilters,
            'seo' => $this->itemIndexSeoData->build([
                'pageTitle' => $tableConfig['title'],
                'category' => $category,
                'type' => $type,
                'total' => $total,
                'manufacturer' => $manufacturer,
            ], $request),
        ]);
    }

    public function show(Request $request, string $item): View|RedirectResponse
    {
        $include = array_filter(array_map('trim', explode(',', (string) $request->query('include', ''))));
        $include = array_values(array_unique(array_merge($include, ['related_items', 'blueprints', 'vehicles'])));

        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', implode(',', $include));

        $payload = $this->apiJsonRequest->request(route('items.show', ['identifier' => $item], false), $apiRequest);

        if (isset($payload['__redirect'])) {
            $path = parse_url($payload['__redirect'], PHP_URL_PATH) ?? '';
            $uuid = basename($path);

            if ($uuid !== '') {
                return redirect(route('web.vehicles.show', ['vehicle' => $uuid]));
            }
        }

        $itemData = Arr::get($payload, 'data', []);

        if ($itemData === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('items.show', [
            'item' => $itemData,
            'itemMeta' => Arr::get($payload, 'meta', []),
            'pageTitle' => Arr::get($itemData, 'name', 'Item'),
            'seo' => $this->itemShowSeoData->build($itemData, $request),
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
}
