<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 10:31
 */

namespace App\Transformers\Api\V1\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use League\Fractal\TransformerAbstract;

/**
 * Image Transformer
 */
class ImageTransformer extends TransformerAbstract
{
    /**
     * @param \App\Models\Rsi\CommLink\Image\Image $image
     *
     * @return array
     */
    public function transform(Image $image): array
    {
        return [
            'src' => $image->src,
            'alt' => $image->alt,
        ];
    }
}
