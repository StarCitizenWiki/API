<?php declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\CommLink;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class CommLinkController
 */
class CommLinkController extends ApiController
{
    /**
     * CommLinkController constructor.
     *
     * @param Request             $request
     * @param CommLinkTransformer $transformer
     */
    public function __construct(Request $request, CommLinkTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Comm-Links
     *
     * @return Response
     */
    public function index(): Response
    {
        $commLinks = CommLink::query()->orderByDesc('cig_id');

        return $this->getResponse($commLinks);
    }

    /**
     * Returns a singular comm-link by its cig_id
     *
     * @param int $commLink
     *
     * @return Response
     */
    public function show(int $commLink): Response
    {
        try {
            $commLink = CommLink::query()->where('cig_id', $commLink)->firstOrFail();
            $commLink->append(['prev', 'next']);
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $commLink));
        }

        // Don't include translation per default
        $this->transformer->setDefaultIncludes(array_slice($this->transformer->getAvailableIncludes(), 0, 2));

        $this->extraMeta = [
            'prev_id' => optional($commLink->prev)->cig_id ?? -1,
            'next_id' => optional($commLink->next)->cig_id ?? -1,
        ];

        return $this->getResponse($commLink);
    }
}
