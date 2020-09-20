<?php declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use League\Fractal\Resource\Primitive;

/**
 * Image Transformer
 */
class ImageTransformer extends V1Transformer
{
    protected $availableIncludes = [
        'hashes',
    ];

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
            'size' => $image->metadata->size,
            'mime_type' => $image->metadata->mime,
            'last_modified' => $image->metadata->last_modified,
        ];
    }

    /**
     * @param Image $image
     *
     * @return Primitive
     */
    public function includeHashes(Image $image): Primitive
    {
        return $this->primitive(
            [
                'perceptual_hash' => $image->hash->perceptual_hash,
                'difference_hash' => $image->hash->difference_hash,
                'average_hash' => $image->hash->average_hash,
            ]
        );
    }
}
