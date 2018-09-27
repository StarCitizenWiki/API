<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 16:03
 */

namespace App\Http\Controllers\Api\V1\StarCitizen\Manufacturer;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Transformers\Api\V1\StarCitizen\Manufacturer\ManufacturerTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Hersteller API
 */
class ManufacturerController extends ApiController
{
    /**
     * ManufacturerController constructor.
     *
     * @param \Illuminate\Http\Request                                                  $request
     * @param \App\Transformers\Api\V1\StarCitizen\Manufacturer\ManufacturerTransformer $transformer
     */
    public function __construct(Request $request, ManufacturerTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Alle Hersteller
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        return $this->getResponse(Manufacturer::query());
    }

    /**
     * Einzelner Hersteller
     *
     * @param string $manufacturer
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $manufacturer)
    {
        $manufacturer = urldecode($manufacturer);

        try {
            $manufacturer = Manufacturer::where('name_short', $manufacturer)->orWhere(
                'name',
                $manufacturer
            )->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $manufacturer));
        }

        return $this->getResponse($manufacturer);
    }

    /**
     * Search Endpoint
     *
     * @return \Dingo\Api\Http\Response
     */
    public function search()
    {
        $query = $this->request->get('query');
        $query = urldecode($query);
        $queryBuilder = Manufacturer::where('name_short', 'like', "%{$query}%")->orWhere('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
