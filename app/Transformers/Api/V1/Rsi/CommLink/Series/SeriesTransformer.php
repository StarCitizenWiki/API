<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Series;

use App\Models\Rsi\CommLink\Series\Series;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_series',
    title: 'Comm-Link Series',
    description: 'Series of a Comm-Link',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'api_url', type: 'string'),
    ],
    type: 'object'
)]
class SeriesTransformer extends V1Transformer
{
    /**
     * @param Series $series
     *
     * @return array
     */
    public function transform(Series $series): array
    {
        return [
            'name' => $series->name,
            'slug' => $series->slug,
            'api_url' => $this->makeApiUrl(self::COMM_LINKS_SERIES_SHOW, $series->getRouteKey()),
        ];
    }
}
