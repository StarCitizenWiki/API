<?php declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use App\Transformers\Api\V1\Rsi\CommLink\CommLinkLinkTransformer;
use League\Fractal\Resource\Collection;

/**
 * Image Transformer
 */
class ImageHashTransformer extends ImageTransformer
{
    protected $availableIncludes = [
        'hashes',
        'commLinks',
    ];

    /**
     * @param Image $image
     *
     * @return array
     */
    public function transform(Image $image): array
    {
        $data = parent::transform($image);

        if (isset($image->similarity)) {
            $data['similatiry'] = $image->similarity;
        }

        return $data;
    }

    /**
     * @param Image $image
     *
     * @return Collection
     */
    public function includeCommLinks(Image $image): Collection
    {
        $links = $image->commLinks;

        return $this->collection($links, new CommLinkLinkTransformer());
    }
}
