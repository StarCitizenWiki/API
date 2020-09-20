<?php declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Link;

use App\Models\Rsi\CommLink\Link\Link;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

/**
 * Image Transformer
 */
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
