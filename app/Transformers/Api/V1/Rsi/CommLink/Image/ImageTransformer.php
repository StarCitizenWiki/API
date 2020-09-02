<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use League\Fractal\TransformerAbstract;

/**
 * Image Transformer
 */
class ImageTransformer extends TransformerAbstract
{
    /**
     * @param Image $image
     *
     * @return array
     */
    public function transform(Image $image): array
    {
        return [
            'rsi_url' => $image->url,
            'api_url' => $image->local ? asset("storage/comm_link_images/{$image->dir}/{$image->name}") : null,
            'alt' => $image->alt,
        ];
    }
}
