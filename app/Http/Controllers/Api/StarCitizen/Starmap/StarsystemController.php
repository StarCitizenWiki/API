<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\StarCitizen\Starmap\StarsystemResource;
use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;

class StarsystemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Starsystem::class, $request)
            ->allowedIncludes([])
            ->paginate()
            ->appends(request()->query());

        return StarsystemResource::collection($query);
    }

    public function show(Request $request): StarsystemResource
    {
        ['code' => $code] = Validator::validate(
            [
                'code' => $request->code,
            ],
            [
                'code' => 'required|string|min:1|max:255',
            ]
        );

        $code = mb_strtoupper(urldecode($code));

        /** @var Starsystem $starsystem */
        $starsystem = QueryBuilder::for(Starsystem::class, $request)
            ->where('code', $code)
            ->orWhere('cig_id', $code)
            ->orWhere('name', 'LIKE', "%$code%")
            ->allowedIncludes(StarsystemResource::validIncludes())
            ->firstOrFail();

        return new StarsystemResource($starsystem);
    }

    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = mb_strtoupper($request->validated('query'));

        $starsystems = QueryBuilder::for(Starsystem::class)
            ->where('code', $query)
            ->orWhere('cig_id', $query)
            ->orWhere('name', 'LIKE', "%$query%")
            ->paginate()
            ->appends(request()->query());

        return StarsystemResource::collection($starsystems);
    }
}
