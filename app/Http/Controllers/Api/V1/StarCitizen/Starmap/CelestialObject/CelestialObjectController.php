<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\CelestialObject;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Transformers\Api\V1\StarCitizen\Starmap\CelestialObjectTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * @param String $code
     *
     * @return Response
     */
    public function show(string $code): Response
    {
        $code = urldecode($code);

        try {
            /** @var CelestialObject $celestialObject */
            $celestialObject = CelestialObject::where('code', $code)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $code));
        }

        return $this->getResponse($celestialObject);
    }

    //TODO weitere Funktionen

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(CelestialObject::query());
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
        $queryBuilder = CelestialObject::where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
