<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Vehicle\ShipSearchRequest;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Raumschiff API
 * Ausgabe der Raumschiffe der Ship Matrix
 */
class ShipController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param ShipTransformer $transformer
     * @param Request         $request
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
     * @return Response
     */
    public function show(string $ship): Response
    {
        $ship = urldecode($ship);

        try {
            $ship = Ship::query()
                ->where('name', $ship)
                ->orWhere('slug', $ship)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $ship));
        }

        return $this->getResponse($ship);
    }

    /**
     * Alle Raumschiffe
     * Ausgabe aller Raumschiffe der Ship Matrix paginiert
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(Ship::query());
    }

    /**
     * Search Endpoint
     *
     * @return Response
     */
    public function search(Request $request): Response
    {
        $rules = (new ShipSearchRequest())->rules();

        $request->validate($rules);

        $query = urldecode($request->get('query'));
        $queryBuilder = Ship::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
