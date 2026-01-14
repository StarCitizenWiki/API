<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Support\Items\ItemTableConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(
        private readonly ApiJsonRequest $apiJsonRequest,
        private readonly ItemTableConfig $itemTableConfig,
    ) {}

    public function index(Request $request, ?string $type = null): View
    {
        $knownCategories = ['armor', 'clothes', 'food', 'weapon-attachments'];
        $resolvedType = is_string($type) ? trim($type) : null;
        $normalizedType = $resolvedType !== null && $resolvedType !== '' ? Str::lower($resolvedType) : null;
        $isCategory = $normalizedType !== null && in_array($normalizedType, $knownCategories, true);

        $routeName = $request->route()?->getName() ?? '';
        $isVehicleItems = str_starts_with($routeName, 'web.vehicle-items.');
        $isVehicleFlairItems = str_starts_with($routeName, 'web.vehicle-flair-items.');

        $categoryRouteMap = [
            'armor' => 'armor.index',
            'clothes' => 'clothes.index',
            'food' => 'food.index',
            'weapon-attachments' => 'attachments.index',
        ];

        $categoryFiltersRouteMap = [
            'armor' => 'armor.filters',
            'clothes' => 'clothes.filters',
            'food' => 'food.filters',
            'weapon-attachments' => 'attachments.filters',
        ];

        if (! empty($resolvedType) && ! $isCategory) {
            $request->merge([
                'filter' => array_merge(
                    $request->input('filter', []),
                    ['type' => $resolvedType]
                ),
            ]);
        }

        if ($isCategory) {
            $indexRouteName = $categoryRouteMap[$normalizedType];
            $filtersRouteName = $categoryFiltersRouteMap[$normalizedType];

            $resolvedType = $normalizedType;
        } elseif ($isVehicleItems) {
            $indexRouteName = 'vehicle-items.index';
            $filtersRouteName = 'vehicle-items.filters';

            $resolvedType = 'vehicle-items';
        } elseif ($isVehicleFlairItems) {
            $indexRouteName = 'vehicle-flair-items.index';
            $filtersRouteName = 'vehicle-items.filters';

            $resolvedType = 'vehicle-flair-items';
        } else {
            $indexRouteName = 'items.index';
            $filtersRouteName = 'items.filters';
        }

        $initialTableData = $this->apiJsonRequest->request(route($indexRouteName, [], false), $request);
        $filterPayload = $this->apiJsonRequest->request(route($filtersRouteName, [], false), $request);

        $filterOptions = Arr::get($filterPayload, 'filters', []);

        $tableConfig = $this->itemTableConfig->build($resolvedType);

        return view('items.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $filterOptions,
            'initialFilters' => ($isCategory || $isVehicleFlairItems) ? [] : $this->buildInitialFilters($resolvedType),
            'pageTitle' => $tableConfig['title'],
            'tableColumns' => $tableConfig['columns'],
            'headerFilterOptionsMap' => $tableConfig['headerFilterOptionsMap'],
            'endpointRouteName' => $indexRouteName,
        ]);
    }

    public function show(string $item): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }

    private function buildInitialFilters(?string $type): array
    {
        if ($type === null || $type === '') {
            return [];
        }

        return [[
            'field' => 'type',
            'value' => $type,
        ]];
    }
}
