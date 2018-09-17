<?php
/**
 * User: Keonie
 * Date: 07.08.2018 14:31
 */

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\CelestialObject;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Transformers\Api\V1\StarCitizen\Starmap\CelestialObjectTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Class CelestialObjectController
 * @package App\Http\Controllers\Api\V1\StarCitizen\Starmap
 */
class CelestialObjectController extends ApiController
{
    /**
     * @var \App\Transformers\Api\V1\StarCitizen\Starmap\CelestialObjectTransformer
     */
    protected $transformer;

    /**
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * CelestialObjectController constructor.
     *
     * @param \App\Transformers\Api\V1\StarCitizen\Starmap\CelestialObjectTransformer $transformer
     * @param \Illuminate\Http\Request                                                $request
     */
    public function __construct(CelestialObjectTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;
        $this->request = $request;

        parent::__construct($request);
    }

    /**
     * @param String $code
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(String $code)
    {
        $code = urldecode($code);

        try {
            $celestialObject = CelestialObject::where('code', $code)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf('No Celestial Object found for Query: %s', $code));
        }
        return $this->getResponse($celestialObject);
    }

    //TODO weitere Funktionen

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->getResponse(CelestialObject::query());
    }

    /**
     * Search Endpoint
     *
     * @return \Dingo\Api\Http\Response
     */
    public function search()
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