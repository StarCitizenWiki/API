<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class CommLinkController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $initialTableData = $this->apiJsonRequest->request(route('comm-links.index', [], false), $request);
        $filterPayload = $this->apiJsonRequest->request(route('comm-links.filters', [], false), $request);

        $allowedFilterValues = Arr::get($filterPayload, 'filters', []);

        return view('comm-links.index', [
            'initialTableData' => $initialTableData,
            'initialHeaderFilter' => $allowedFilterValues,
        ]);
    }
}
