<?php

declare(strict_types=1);

namespace App\Http\Resources\Rsi\CommLink\Image;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_image',
    title: 'Comm-Link Image',
    description: 'An image embedded in a Comm-Link, sourced from the RSI media CDN.',
    properties: [
        new OA\Property(property: 'id', description: 'Internal database identifier.', type: 'integer'),
        new OA\Property(property: 'name', description: 'File name or path segment of the image.', type: 'string', example: 'Starshipbridge.jpg'),
        new OA\Property(property: 'rsi_url', description: 'Full URL to the image on the RSI media CDN.', type: 'string', format: 'uri', example: '/media/bluo97w6u7n1ur/source/Starshipbridge.jpg'),
        new OA\Property(property: 'api_url', description: 'API URL for this image resource.', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'alt', description: 'Alternative text for the image.', type: 'string'),
        new OA\Property(property: 'size', description: 'File size in bytes.', type: 'integer', example: 1504015),
        new OA\Property(property: 'mime_type', description: 'MIME type of the image file.', type: 'string', example: 'image/jpeg'),
        new OA\Property(property: 'last_modified', description: 'ISO 8601 timestamp of when the image was last modified on the CDN.', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'tags',
            description: 'Tags associated with the image when the `tags` relation is loaded.',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', description: 'Tag name.', type: 'string'),
                    new OA\Property(property: 'translated_name', description: 'Translated tag name.', type: 'string', nullable: true),
                    new OA\Property(property: 'images_count', description: 'Number of images sharing this tag.', type: 'integer'),
                ],
            ),
            nullable: true,
        ),
        new OA\Property(property: 'similar_url', description: 'API URL to find visually similar images.', type: 'string', format: 'uri'),
        new OA\Property(
            property: 'comm_links',
            description: 'Comm-Links that reference this image, included when the `commLinks` relation is loaded.',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', description: 'CIG identifier of the Comm-Link', type: 'integer'),
                    new OA\Property(property: 'title', description: 'Title of the Comm-Link', type: 'string'),
                    new OA\Property(property: 'api_url', description: 'API URL for this Comm-Link resource.', type: 'string', format: 'uri'),
                    new OA\Property(property: 'web_url', description: 'Public web URL for this Comm-Link.', type: 'string', format: 'uri'),
                ],
            ),
            nullable: true,
        ),
        new OA\Property(
            property: 'duplicates',
            description: 'Duplicate images that share the same base image, included when the `duplicates` relation is loaded.',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', description: 'Internal database ID of the duplicate image', type: 'integer'),
                    new OA\Property(property: 'name', description: 'File name of the duplicate image', type: 'string'),
                ],
            ),
            nullable: true,
        ),
        new OA\Property(
            property: 'base_image',
            description: 'The original image this is a duplicate of, included when the `baseImage` relation is loaded.',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', description: 'Internal database ID of the base image', type: 'integer'),
                new OA\Property(property: 'name', description: 'File name of the base image', type: 'string'),
            ],
            nullable: true,
        ),
        new OA\Property(
            property: 'similarity',
            description: 'Similarity percentage to the search image. Only present in reverse image search results.',
            type: 'integer',
            nullable: true,
        ),
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
