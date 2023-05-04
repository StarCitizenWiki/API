<?php

declare(strict_types=1);

namespace App\Http\Resources\Rsi\CommLink;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\TranslationCollection;
use App\Http\Resources\Rsi\CommLink\Image\ImageResource;
use App\Http\Resources\Rsi\CommLink\Link\LinkResource;
use App\Http\Resources\TranslationResourceFactory;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_v2',
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
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/comm_link_image_v2'),
        ),
        new OA\Property(property: 'images_count', type: 'integer'),
        new OA\Property(
            property: 'links',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/comm_link_content_link_v2'),
        ),
        new OA\Property(property: 'links_count', type: 'integer'),
        new OA\Property(property: 'comment_count', type: 'integer'),
        new OA\Property(property: 'created_at', type: 'timestamp'),
        new OA\Property(
            property: 'translations',
            ref: '#/components/schemas/translation_v2',
            nullable: true
        ),
    ],
    type: 'json'
)]
class CommLinkResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'images',
            'links',
            'translations'
        ];
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->cig_id,
            'title' => $this->title,
            'rsi_url' => $this->getCommLinkUrl(),
            'api_url' => $this->makeApiUrl(self::COMM_LINKS_SHOW, $this->getRouteKey()),
            'api_public_url' => route('web.api.comm-links.show', $this->getRouteKey()),
            'channel' => $this->channel->name,
            'category' => $this->category->name,
            'series' => $this->series->name,
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'images_count' => $this->images_count,
            'translations' => TranslationResourceFactory::getTranslationResource($request, $this->whenLoaded('translations')),
            'links' => LinkResource::collection($this->whenLoaded('links')),
            'links_count' => $this->links_count,
            'comment_count' => $this->comment_count,
            'created_at' => $this->created_at,
        ];
    }


    /**
     * If no URL is set a default url will be returned
     *
     * @return string
     */
    private function getCommLinkUrl(): string
    {
        return sprintf('%s%s', config('api.rsi_url'), ($this->url ?? "/comm-link/SCW/{$this->cig_id}-API"));
    }
}
