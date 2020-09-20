<?php declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Channel;

use App\Models\Rsi\CommLink\Channel\Channel;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

/**
 * Channel Transformer
 */
class ChannelTransformer extends V1Transformer
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
            'api_url' => $this->makeApiUrl(self::COMM_LINKS_CHANNELS_SHOW, $channel->getRouteKey()),
        ];
    }
}
