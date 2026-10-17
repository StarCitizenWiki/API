<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Traits\MergesIncludeParam;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\View\View;

#[CacheTag('galactapedia')]
class GalactapediaController extends Controller
{
    use MergesIncludeParam;

    public function __construct(private readonly ApiJsonRequest $apiJsonRequest) {}

    public function index(Request $request): View
    {
        return view('galactapedia.index');
    }

    public function show(Request $request, string $article): View
    {
        $apiRequest = $request->duplicate();
        $apiRequest->query->set('include', $this->mergeInclude($request, ['categories', 'tags', 'properties', 'related']));

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
