<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
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

class CelestialObjectController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(CelestialObject::class, $request)
            ->allowedIncludes([])
            ->paginate()
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

    public function search(StarsystemRequest $request): AnonymousResourceCollection
    {
        $query = $this->cleanQueryName($request->get('query'));

        $objects = QueryBuilder::for(CelestialObject::class)
            ->where('code', $query)
            ->orWhere('cig_id', $query)
            ->orWhere('name', 'LIKE', "%$query%")
            ->paginate()
            ->appends(request()->query());

        return CelestialObjectResource::collection($objects);
    }
}
