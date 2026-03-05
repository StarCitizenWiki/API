<?php

declare(strict_types=1);

namespace App\Http\Resources\Rsi\CommLink\Image;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_image',
    title: 'Comm-Link Image',
    description: 'Image used in a Comm-Link',
    properties: [
        new OA\Property(property: 'rsi_url', type: 'string'),
        new OA\Property(property: 'api_url', type: 'string', nullable: true),
        new OA\Property(property: 'alt', type: 'string'),
        new OA\Property(property: 'size', type: 'integer'),
        new OA\Property(property: 'mime_type', type: 'string'),
        new OA\Property(property: 'last_modified', type: 'string'),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true,
        ),
        new OA\Property(property: 'similar_url', type: 'string'),
    ],
    type: 'object'
)]
class ImageResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rsi_url' => $this->url,
            'alt' => $this->alt,
            'size' => $this->metadata->size,
            'mime_type' => $this->metadata->mime,
            'last_modified' => $this->metadata->last_modified->toIso8601String(),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag) => [
                'name' => $tag->name,
                'translated_name' => $tag->translated_name,
                'images_count' => $tag->images_count,
            ])),
            'comm_links' => $this->whenLoaded('commLinks', fn () => $this->commLinks->map(fn ($commLink) => [
                'id' => $commLink->cig_id,
                'title' => $commLink->title,
                'api_url' => route('comm-links.show', ['id' => $commLink->cig_id]),
                'web_url' => route('web.comm-links.show', $commLink->cig_id),
            ])),
            'duplicates' => $this->whenLoaded('duplicates', fn () => $this->duplicates->map(fn ($image) => [
                'id' => $image->id,
                'name' => $image->name,
            ])),
            'base_image' => $this->whenLoaded('baseImage', fn () => $this->baseImage === null ? null : [
                'id' => $this->baseImage->id,
                'name' => $this->baseImage->name,
            ]),
            'api_url' => route('comm-link-images.show', ['image' => $this->getRouteKey()]),
            'similar_url' => route('comm-link-images.similar', ['image' => $this->getRouteKey()]),
        ];
    }
}
