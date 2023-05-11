<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\StarCitizen\Starmap;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\StarCitizen\Starmap\StarsystemRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\StarCitizen\Starmap\CelestialObjectResource;
use App\Models\StarCitizen\Starmap\CelestialObject\CelestialObject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CelestialObjectController extends AbstractApiV2Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(CelestialObject::class, $request)
            ->allowedIncludes([])
            ->paginate($this->limit)
            ->appends(request()->query());

        return CelestialObjectResource::collection($query);
    }


    public function show(Request $request): AbstractBaseResource
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
            /** @var CelestialObject $starsystem */
            $starsystem = QueryBuilder::for(CelestialObject::class, $request)
                ->where('code', $code)
                ->orWhere('cig_id', $code)
                ->orWhere('name', 'LIKE', "%$code%")
                ->allowedIncludes(CelestialObjectResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Celestial Object with specified Code or Name found.');
        }

        return new CelestialObjectResource($starsystem);
    }


    public function search(Request $request): AnonymousResourceCollection
    {
        $rules = (new StarsystemRequest())->rules();
        $request->validate($rules);

        $query = $this->cleanQueryName($request->get('query'));

        $objects = QueryBuilder::for(CelestialObject::class)
            ->where('code', $query)
            ->orWhere('cig_id', $query)
            ->orWhere('name', 'LIKE', "%$query%")
            ->paginate($this->limit)
            ->appends(request()->query());

        if ($objects->count() === 0) {
            throw new NotFoundHttpException(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return CelestialObjectResource::collection($objects);
    }
}
