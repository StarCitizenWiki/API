<?php
/**
 * User: Keonie
 * Date: 07.08.2018 14:31
 */

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap;

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
    private $transformer;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

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

        if ($request->has('locale')) {
            $this->transformer->setLocale($request->get('locale'));
        }
    }

    /**
     * @param String $code
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(String $code)
    {
        try {
            $celestialObject = CelestialObject::where('code', $code)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf('No Jumppoint found for Query: %s', $id));
        }
        return $this->response->item($celestialObject, $this->transformer);
    }

    //TODO weitere Funktionen

    /**
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $celestialObjects = CelestialObject::paginate();
        return $this->response->paginator($celestialObjects, $this->transformer);
    }
}