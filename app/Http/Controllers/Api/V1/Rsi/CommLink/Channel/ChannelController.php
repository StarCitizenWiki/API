<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 10:29
 */

namespace App\Http\Controllers\Api\V1\Rsi\CommLink\Channel;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Transformers\Api\V1\Rsi\CommLink\Channel\ChannelTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Class Channel Controller
 */
class ChannelController extends ApiController
{
    /**
     * Comm-Link Transformer
     *
     * @var \App\Transformers\Api\V1\Rsi\CommLink\Channel\ChannelTransformer
     */
    protected $transformer;

    /**
     * StatsAPIController constructor.
     *
     * @param \Illuminate\Http\Request                                         $request
     * @param \App\Transformers\Api\V1\Rsi\CommLink\Channel\ChannelTransformer $transformer
     */
    public function __construct(Request $request, ChannelTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    /**
     * Ausgabe aller Channel
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index()
    {
        $categories = Channel::query()->orderBy('name');

        return $this->getResponse($categories);
    }

    /**
     * @param string $channel
     *
     * @return \Dingo\Api\Http\Response
     */
    public function show(string $channel)
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
