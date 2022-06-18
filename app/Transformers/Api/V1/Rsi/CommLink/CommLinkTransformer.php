<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use App\Transformers\Api\V1\Rsi\CommLink\Image\ImageTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\Link\LinkTransformer;
use App\Transformers\Api\V1\Rsi\CommLink\Translation\TranslationTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link',
    title: 'Comm-Link',
    description: 'A Comm-Link',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'rsi_url', type: 'string'),
        new OA\Property(property: 'api_url', type: 'string'),
        new OA\Property(property: 'api_public_url', type: 'string'),
        new OA\Property(property: 'channel', type: 'string'),
        new OA\Property(property: 'category', type: 'string'),
        new OA\Property(property: 'series', type: 'string'),
        new OA\Property(
            property: 'images',
            oneOf: [
                new OA\Schema(properties: [new OA\Property(type: 'integer')]),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'images',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/comm_link_image',
                                    type: 'array',
                                    items: new OA\Items(),
                                ),
                            ],
                            type: 'object',
                            nullable: true
                        ),
                    ],
                    type: 'object'
                ),
            ]
        ),
        new OA\Property(
            property: 'links',
            oneOf: [
                new OA\Schema(properties: [new OA\Property(type: 'integer')]),
                new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'links',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    ref: '#/components/schemas/comm_link_content_link',
                                    type: 'array',
                                    items: new OA\Items(),
                                ),
                            ],
                            type: 'object',
                            nullable: true
                        ),
                    ],
                    type: 'object'
                )
            ]
        ),
        new OA\Property(property: 'comment_count', type: 'integer'),
        new OA\Property(property: 'created_at', type: 'timestamp'),
        new OA\Property(
            property: 'english',
            properties: [
                new OA\Property(
                    property: 'data',
                    ref: '#/components/schemas/comm_link_translation',
                ),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class CommLinkTransformer extends V1Transformer
{
    protected $availableIncludes = [
        'images',
        'links',
        'english',
        'german',
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
            'api_url' => $this->makeApiUrl(self::COMM_LINKS_SHOW, $commLink->getRouteKey()),
            'api_public_url' => route('web.api.comm-links.show', $commLink->getRouteKey()),
            'channel' => $commLink->channel->name,
            'category' => $commLink->category->name,
            'series' => $commLink->series->name,
            'images' => $commLink->images_count,
            'links' => $commLink->links_count,
            'comment_count' => $commLink->comment_count,
            'created_at' => $commLink->created_at,
        ];
    }

    /**
     * If no URL is set a default url will be returned
     *
     * @param CommLink $commLink
     *
     * @return string
     */
    private function getCommLinkUrl(CommLink $commLink): string
    {
        return sprintf('%s%s', config('api.rsi_url'), ($commLink->url ?? "/comm-link/SCW/{$commLink->cig_id}-API"));
    }

    /**
     * @param CommLink $commLink
     *
     * @return Collection
     */
    public function includeImages(CommLink $commLink): Collection
    {
        $images = $commLink->images;

        return $this->collection($images, new ImageTransformer());
    }

    /**
     * @param CommLink $commLink
     *
     * @return Collection
     */
    public function includeLinks(CommLink $commLink): Collection
    {
        $links = $commLink->links;

        return $this->collection($links, new LinkTransformer());
    }

    /**
     * @param CommLink $commLink
     *
     * @return Item
     */
    public function includeEnglish(CommLink $commLink): Item
    {
        $translation = $commLink->english();

        return $this->item($translation, new TranslationTransformer());
    }

    /**
     * @param CommLink $commLink
     *
     * @return Item
     */
    public function includeGerman(CommLink $commLink): Item
    {
        //$translation = $commLink->german();
        $translation = null; // Disable this for now

        return $this->item($translation, new TranslationTransformer());
    }
}
