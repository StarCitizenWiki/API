<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Channel;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Transformers\Api\V1\Rsi\CommLink\Channel\ChannelTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class Channel Controller
 */
class ChannelController extends ApiController
{
    /**
     * StatsAPIController constructor.
     *
     * @param Request            $request
     * @param ChannelTransformer $transformer
     */
    public function __construct(Request $request, ChannelTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Channel
     *
     * @return Response
     */
    public function index(): Response
    {
        $categories = Channel::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    /**
     * @param string $channel
     *
     * @return Response
     */
    public function show(string $channel): Response
    {
        try {
            $channel = Channel::query()->where('name', $channel)->orWhere('slug', $channel)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $channel));
        }

        $this->transformer = new CommLinkTransformer();

        return $this->getResponse($channel->commLinks()->orderByDesc('cig_id'));
    }
}
