<?php

namespace App\Http\Resources\Rsi\CommLink\Image;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
            property: 'hashes',
            properties: [
                new OA\Property(property: 'perceptual_hash', type: 'integer'),
                new OA\Property(property: 'difference_hash', type: 'integer'),
                new OA\Property(property: 'average_hash', type: 'integer'),
            ],
            type: 'object',
            nullable: true
        )
    ],
    type: 'object'
)]
class ImageResource extends JsonResource
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
            'hashes' => $this->whenLoaded('hash', [
                'perceptual_hash' => $this->hash->perceptual_hash,
                'difference_hash' => $this->hash->difference_hash,
                'average_hash' => $this->hash->average_hash,
            ])
        ];
    }
}
