<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Link;

use App\Models\Rsi\CommLink\Link\Link;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_content_link',
    title: 'Comm-Link Content Link',
    description: 'Link contained in the content of a Comm-Link',
    properties: [
        new OA\Property(property: 'href', description: 'URL', type: 'string'),
        new OA\Property(property: 'text', description: 'Link content', type: 'string'),
    ],
    type: 'object'
)]
class LinkTransformer extends V1Transformer
{
    /**
     * @param Link $link
     *
     * @return array
     */
    public function transform(Link $link): array
    {
        return [
            'href' => $link->href,
            'text' => $link->text,
        ];
    }
}
