<?php

namespace App\Http\Resources\Rsi\CommLink\Image;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_image_v2',
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
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'rsi_url' => $this->url,
            'api_url' => $this->local ? asset("storage/comm_link_images/{$this->dir}/{$this->name}") : null,
            'alt' => $this->alt,
            'size' => $this->metadata->size,
            'mime_type' => $this->metadata->mime,
            'last_modified' => $this->metadata->last_modified,
            $this->mergeWhen($this->whenLoaded('tags'), [
                'tags' => $this->tags->map(fn ($tag) => $tag->translated_name)
            ]),
            'similar_url' => $this->makeApiUrl(static::COMM_LINK_IMAGES_SIMILAR, $this->getRouteKey() . '/similar')
        ];
    }
}
