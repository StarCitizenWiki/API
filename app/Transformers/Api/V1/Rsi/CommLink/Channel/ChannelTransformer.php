<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Channel;

use App\Models\Rsi\CommLink\Channel\Channel;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use JetBrains\PhpStorm\ArrayShape;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_channel',
    title: 'Comm-Link Channel',
    description: 'Channel of a Comm-Link',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'api_url', type: 'string'),
    ],
    type: 'object'
)]
class ChannelTransformer extends V1Transformer
{
    /**
     * @param Channel $channel
     *
     * @return array
     */
    #[ArrayShape(['name' => "mixed", 'slug' => "mixed", 'api_url' => "string"])]
    public function transform(Channel $channel): array
    {
        return [
            'name' => $channel->name,
            'slug' => $channel->slug,
            'api_url' => $this->makeApiUrl(self::COMM_LINKS_CHANNELS_SHOW, $channel->getRouteKey()),
        ];
    }
}
