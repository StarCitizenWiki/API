<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request, ?string $type = null): View
    {
        $requestWithFilters = $this->applyTypeFilter($request, $type);

        $initialTableData = $this->apiJsonRequest->request(route('items.index', [], false), $requestWithFilters);
        $filterPayload = $this->apiJsonRequest->request(route('items.filters', [], false), $requestWithFilters);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('items.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
            'initialFilters' => $this->buildInitialFilters($type),
        ]);
    }

    public function show(string $item): Response
    {
        return response('', Response::HTTP_NO_CONTENT);
    }

    private function applyTypeFilter(Request $request, ?string $type): Request
    {
        if ($type === null || $type === '') {
            return $request;
        }

        $filters = $request->query('filter', []);

        if (! is_array($filters)) {
            $filters = [];
        }

        $filters['type'] = $type;

        $request->merge(['filter' => $filters]);

        return $request;
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
