<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class GalactapediaController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $initialTableData = $this->apiJsonRequest->request(route('galactapedia.index', [], false), $request);
        $filterPayload = $this->apiJsonRequest->request(route('galactapedia.filters', [], false), $request);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('galactapedia.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
        ]);
    }

    public function show(Request $request, string $article): View
    {
        $include = array_filter(array_map('trim', explode(',', (string) $request->query('include', ''))));
        $include = array_values(array_unique(array_merge($include, ['categories', 'tags', 'properties', 'related'])));

        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', implode(',', $include));

        $payload = $this->apiJsonRequest->request(route('galactapedia.show', ['article' => $article], false), $apiRequest);
        $articleData = Arr::get($payload, 'data', []);

        if ($articleData === []) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return view('galactapedia.show', [
            'article' => $articleData,
            'articleMeta' => Arr::get($payload, 'meta', []),
            'pageTitle' => Arr::get($articleData, 'title', 'Galactapedia'),
        ]);
    }
}
