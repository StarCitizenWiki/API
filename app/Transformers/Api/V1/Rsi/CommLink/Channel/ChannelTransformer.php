<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Channel;

use App\Models\Rsi\CommLink\Channel\Channel;
use League\Fractal\TransformerAbstract;

/**
 * Channel Transformer
 */
class ChannelTransformer extends TransformerAbstract
{
    /**
     * @param Channel $channel
     *
     * @return array
     */
    public function transform(Channel $channel): array
    {
        return [
            'name' => $channel->name,
            'slug' => $channel->slug,
            'api_url' => app('api.url')->version('v1')->route(
                'api.v1.rsi.comm-links.channels.show',
                [$channel->getRouteKey()]
            ),
        ];
    }
}
