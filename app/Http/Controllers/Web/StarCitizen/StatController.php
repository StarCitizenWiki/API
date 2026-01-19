<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen;

use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class StatController extends Controller
{
    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        $latestStats = $this->apiJsonRequest->request(route('stats.latest', [], false), $request);

        return view('stats.index', [
            'latestStats' => Arr::get($latestStats, 'data', []),
        ]);
    }
}
