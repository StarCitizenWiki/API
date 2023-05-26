<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\CelestialObject;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Transformers\Api\V1\StarCitizen\Starmap\CelestialObjectTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class CelestialObjectController
 */
class CelestialObjectController extends ApiController
{
    /**
     * CelestialObjectController constructor.
     *
     * @param Request                    $request
     * @param CelestialObjectTransformer $transformer
     */
    public function __construct(Request $request, CelestialObjectTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(CelestialObject::query());
    }

    /**
     * @param string|int $code
     *
     * @return Response
     */
    public function show($code): Response
    {
        $code = urldecode($code);

        try {
            /** @var CelestialObject $celestialObject */
            $celestialObject = CelestialObject::query()
                ->where('code', $code)
                ->orWhere('cig_id', $code)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $code)], 404);
        }

        return $this->getResponse($celestialObject);
    }

    /**
     * Search Endpoint
     *
     * @return Response
     */
    public function search(): Response
    {
        $query = $this->request->get('query', '');
        $query = urldecode($query);
        $queryBuilder = CelestialObject::query()->where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $query)], 404);
        }

        return $this->getResponse($queryBuilder);
    }
}
