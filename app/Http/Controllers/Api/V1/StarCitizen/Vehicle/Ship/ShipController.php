<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Raumschiff API
 * Ausgabe der Raumschiffe der Ship Matrix
 */
class ShipController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param \App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer $transformer
     * @param \Illuminate\Http\Request                                          $request
     */
    public function __construct(ShipTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    /**
     * Einzelnes Raumschiff
     * Ausgabe eines einzelnen Raumschiffes nach Schiffsnamen (z.B. 300i)
     * Name des Schiffes sollte URL enkodiert sein
     *
     * @param string $ship
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $ship)
    {
        $ship = urldecode($ship);

        try {
            $ship = Ship::query()->where('name', $ship)->orWhere('slug', $ship)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $ship));
        }

        return $this->getResponse($ship);
    }

    /**
     * Alle Raumschiffe
     * Ausgabe aller Raumschiffe der Ship Matrix paginiert
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->getResponse(Ship::query());
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
        $queryBuilder = Ship::query()->where('name', 'like', "%{$query}%")->orWhere('slug', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
