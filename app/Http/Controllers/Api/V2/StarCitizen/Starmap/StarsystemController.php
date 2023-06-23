<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\StarCitizen\Starmap;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\StarCitizen\Starmap\StarsystemRequest;
use App\Http\Resources\StarCitizen\Starmap\StarsystemResource;
use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StarsystemController extends AbstractApiV2Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Starsystem::class, $request)
            ->allowedIncludes([])
            ->paginate($this->limit)
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

        try {
            /** @var Starsystem $starsystem */
            $starsystem = QueryBuilder::for(Starsystem::class, $request)
                ->where('code', $code)
                ->orWhere('cig_id', $code)
                ->orWhere('name', 'LIKE', "%$code%")
                ->allowedIncludes(StarsystemResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Starsystem with specified Code or Name found.');
        }

        return new StarsystemResource($starsystem);
    }


    public function search(Request $request): AnonymousResourceCollection
    {
        $rules = (new StarsystemRequest())->rules();
        $request->validate($rules);

        $query = $this->cleanQueryName($request->get('query'));

        $starsystems = QueryBuilder::for(Starsystem::class)
            ->where('code', $query)
            ->orWhere('cig_id', $query)
            ->orWhere('name', 'LIKE', "%$query%")
            ->paginate($this->limit)
            ->appends(request()->query());

        if ($starsystems->count() === 0) {
            throw new NotFoundHttpException(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return StarsystemResource::collection($starsystems);
    }
}
