<?php

declare(strict_types=1);

namespace App\Http\Resources\Rsi\CommLink;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Rsi\CommLink\Image\ImageResource;
use App\Http\Resources\TranslationResolver;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link',
    title: 'Comm-Link',
    description: 'A Spectrum Comm-Link published by Cloud Imperium Games on robertsspaceindustries.com.',
    properties: [
        new OA\Property(property: 'id', description: 'CIG identifier for the Comm-Link', type: 'integer', example: 12663),
        new OA\Property(property: 'title', description: 'Title of the Comm-Link', type: 'string', example: 'This Week in Star Citizen'),
        new OA\Property(property: 'rsi_url', description: 'Link to the Comm-Link on the RSI website.', type: 'string', format: 'uri'),
        new OA\Property(property: 'api_url', description: 'API URL for this Comm-Link resource.', type: 'string', format: 'uri'),
        new OA\Property(property: 'api_public_url', description: 'Public web URL for this Comm-Link on the API portal.', type: 'string', format: 'uri'),
        new OA\Property(property: 'channel', description: 'Publishing channel, e.g. Engineering, Transmission.', type: 'string', example: 'Engineering'),
        new OA\Property(property: 'category', description: 'Content category, e.g. General, Community, Lore, Development.', type: 'string', example: 'General'),
        new OA\Property(property: 'series', description: 'Series the Comm-Link belongs to, e.g. Around the Verse, 10 For the Chairman.', type: 'string', example: 'Around the Verse'),
        new OA\Property(
            property: 'images',
            description: 'Included images when the `images` relation is loaded.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/comm_link_image'),
        ),
        new OA\Property(property: 'images_count', description: 'Total number of images associated with this Comm-Link.', type: 'integer', example: 5),
        new OA\Property(
            property: 'links',
            description: 'Included links when the `links` relation is loaded.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/comm_link_link'),
        ),
        new OA\Property(property: 'links_count', description: 'Total number of external links in the Comm-Link content.', type: 'integer', example: 3),
        new OA\Property(property: 'comment_count', description: 'Number of comments on the original RSI post.', type: 'integer', example: 42),
        new OA\Property(property: 'created_at', description: 'ISO 8601 timestamp of when the Comm-Link was published.', type: 'string', format: 'date-time'),
        new OA\Property(property: 'translations', ref: '#/components/schemas/translation'),
        new OA\Property(property: 'created_at_human', description: 'Human-readable relative time since publication.', type: 'string', example: '1 hour ago'),
    ],
    type: 'object'
)]
class CommLinkResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cig_id,
            'title' => $this->title,
            'rsi_url' => $this->getCommLinkUrl(),
            'api_url' => route('comm-links.show', ['id' => $this->getRouteKey()]),
            'api_public_url' => route('web.comm-links.show', $this->getRouteKey()),
            'channel' => $this->channel->name,
            'category' => $this->category->name,
            'series' => $this->series->name,
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'images_count' => $this->images_count,
            'translations' => TranslationResolver::resolve($this, $request),
            'links' => CommLinkLinkResource::collection($this->whenLoaded('links')),
            'links_count' => $this->links_count,
            'comment_count' => $this->comment_count,
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }

    /**
     * If no URL is set a default url will be returned
     */
    private function getCommLinkUrl(): string
    {
        if (str_contains($this->url, 'robertsspaceindustries.com')) {
            return $this->url;
        }

        return sprintf('%s%s', config('services.rsi_url'), ($this->url ?? "/comm-link/SCW/{$this->cig_id}-API"));
    }
}
