<?php declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\CommLink;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use League\Fractal\TransformerAbstract;

/**
 * Class CommLinkController
 */
class CommLinkController extends ApiController
{
    /**
     * Comm-Link Transformer
     *
     * @var CommLinkTransformer
     */
    protected TransformerAbstract $transformer;

    /**
     * StatsAPIController constructor.
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
        $stats = CommLink::orderByDesc('cig_id');

        return $this->getResponse($stats);
    }

    /**
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

        $this->transformer->setDefaultIncludes($this->transformer->getAvailableIncludes());

        $this->extraMeta = [
            'prev_id' => optional($commLink->prev)->cig_id ?? -1,
            'next_id' => optional($commLink->next)->cig_id ?? -1,
        ];

        return $this->getResponse($commLink);
    }
}
