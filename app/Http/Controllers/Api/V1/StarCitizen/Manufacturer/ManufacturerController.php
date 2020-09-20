<?php declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Manufacturer;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Manufacturer\ManufacturerSearchRequest;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Transformers\Api\V1\StarCitizen\Manufacturer\ManufacturerTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Hersteller API
 */
class ManufacturerController extends ApiController
{
    /**
     * ManufacturerController constructor.
     *
     * @param Request                 $request
     * @param ManufacturerTransformer $transformer
     */
    public function __construct(Request $request, ManufacturerTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Alle Hersteller
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->getResponse(Manufacturer::query());
    }

    /**
     * Einzelner Hersteller
     *
     * @param string $manufacturer
     *
     * @return Response
     */
    public function show(string $manufacturer): Response
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
     * @param Request $request
     *
     * @return Response
     */
    public function search(Request $request): Response
    {
        $rules = (new ManufacturerSearchRequest())->rules();
        $request->validate($rules);

        $query = urldecode($request->get('query'));
        $queryBuilder = Manufacturer::query()
            ->where('name_short', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($queryBuilder);
    }
}
