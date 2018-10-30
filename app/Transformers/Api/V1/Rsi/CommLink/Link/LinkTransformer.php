<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 10:31
 */

namespace App\Transformers\Api\V1\Rsi\CommLink\Link;

use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link\Link;
use League\Fractal\TransformerAbstract;

/**
 * Image Transformer
 */
class LinkTransformer extends TransformerAbstract
{
    /**
     * @param \App\Models\Rsi\CommLink\Link\Link $link
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
