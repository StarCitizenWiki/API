<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\StarCitizen;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Services\ApiJsonRequest;
use App\Traits\MergesIncludeParam;
use Illuminate\Http\Request;
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
        $includes = explode(',', $this->mergeInclude($request, ['categories', 'tags', 'properties', 'related']));

        ['data' => $articleData, 'meta' => $articleMeta] = $this->apiJsonRequest->fetchResource(
            route('galactapedia.show', ['article' => $article], false),
            $request,
            $includes,
        );

        return view('galactapedia.show', [
            'article' => $articleData,
            'articleMeta' => $articleMeta,
            'pageTitle' => Arr::get($articleData, 'title', 'Galactapedia'),
        ]);
    }
}
