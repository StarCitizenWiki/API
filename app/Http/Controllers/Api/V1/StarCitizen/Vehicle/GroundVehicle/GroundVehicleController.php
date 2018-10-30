<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Bodenfahrzeug API
 * Ausgabe der Bodenfahrzeuge der Ship Matrix
 */
class GroundVehicleController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param \App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTransformer $transformer
     * @param \Illuminate\Http\Request                                                            $request
     */
    public function __construct(GroundVehicleTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    /**
     * Einzelnes Bodenfahrzeug
     * Ausgabe eines einzelnen Bodenfahrzeuges nach Fahrzeugnamen (z.B. Cyclone)
     * Name des Bodenfahrzeuges sollte URL enkodiert sein
     *
     * @param string $groundVehicle
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $groundVehicle)
    {
        $groundVehicle = urldecode($groundVehicle);

        try {
            $groundVehicle = GroundVehicle::query()->where('name', $groundVehicle)->orWhere('slug', $groundVehicle)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $groundVehicle));
        }

        return $this->getResponse($groundVehicle);
    }

    /**
     * Alle Bodenfahrzeuge
     * Ausgabe aller Bodenfahrzeuge der Ship Matrix paginiert
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->getResponse(GroundVehicle::query());
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
        $queryBuilder = GroundVehicle::query()->where('name', 'like', "%{$query}%")->orWhere('slug', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
