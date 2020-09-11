<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Transformers\Api\V1\Rsi\CommLink\Image\ImageTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\Link\LinkTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

/**
 * Class CommLinkTransformer
 */
class CommLinkTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'images',
        'links',
    ];

    /**
     * @param CommLink $commLink
     *
     * @return array
     */
    public function transform(CommLink $commLink): array
    {
        return [
            'id' => $commLink->cig_id,
            'title' => $commLink->title,
            'rsi_url' => $this->getCommLinkUrl($commLink),
            'api_url' => app('api.url')->version('v1')->route(
                'api.v1.rsi.comm-links.show',
                [$commLink->getRouteKey()]
            ),
            'channel' => $commLink->channel->name,
            'category' => $commLink->category->name,
            'series' => $commLink->series->name,
            'images' => $commLink->images_count,
            'links' => $commLink->links_count,
            'comment_count' => $commLink->comment_count,
            'created_at' => $commLink->created_at->toDateString(),
        ];
    }

    /**
     * @param CommLink $commLink
     *
     * @return Collection
     */
    public function includeImages(CommLink $commLink)
    {
        $images = $commLink->images;

        return $this->collection($images, new ImageTransformer());
    }

    /**
     * @param CommLink $commLink
     *
     * @return Collection
     */
    public function includeLinks(CommLink $commLink)
    {
        $links = $commLink->links;

        return $this->collection($links, new LinkTransformer());
    }

    /**
     * If no URL is set a default url will be returned
     *
     * @param CommLink $commLink
     *
     * @return string
     */
    private function getCommLinkUrl(CommLink $commLink)
    {
        return sprintf('%s%s', config('api.rsi_url'), ($commLink->url ?? "/comm-link/SCW/{$commLink->cig_id}-API"));
    }
}
