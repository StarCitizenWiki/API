<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\Image\Image;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use League\Fractal\Resource\Primitive;
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
        new OA\Property(property: 'last_modified', type: 'timestamp'),
        new OA\Property(
            property: 'hashes',
            properties: [
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'perceptual_hash', type: 'integer'),
                        new OA\Property(property: 'difference_hash', type: 'integer'),
                        new OA\Property(property: 'average_hash', type: 'integer'),
                    ],
                    type: 'array',
                    items: new OA\Items()
                ),
            ],
            type: 'object',
            nullable: true
        )
    ],
    type: 'object'
)]
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
