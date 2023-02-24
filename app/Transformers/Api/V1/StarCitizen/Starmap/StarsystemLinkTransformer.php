<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'starsystem_link',
    title: 'Starsystem Link',
    description: 'Link to the full star system page',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'api_url', type: 'string'),
    ],
    type: 'object'
)]
class StarsystemLinkTransformer extends V1Transformer
{
    /**
     * @param Starsystem $starsystem
     *
     * @return array
     */
    public function transform(Starsystem $starsystem): array
    {
        return [
            'name' => $starsystem->name,
            'code' => $starsystem->code,
            'api_url' => $this->makeApiUrl(self::STARMAP_STARSYSTEM_SHOW, $starsystem->getRouteKey()),
        ];
    }
}
