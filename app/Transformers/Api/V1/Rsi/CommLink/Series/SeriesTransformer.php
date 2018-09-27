<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 10:31
 */

namespace App\Transformers\Api\V1\Rsi\CommLink\Series;

use App\Models\Rsi\CommLink\Series\Series;
use League\Fractal\TransformerAbstract;

/**
 * Series Transformer
 */
class SeriesTransformer extends TransformerAbstract
{
    /**
     * @param \App\Models\Rsi\CommLink\Series\Series $series
     *
     * @return array
     */
    public function transform(Series $series): array
    {
        return [
            'name' => $series->name,
            'url' => app('api.url')->version('v1')->route(
                'api.v1.rsi.comm-links.series.show',
                [$series->getRouteKey()]
            ),
        ];
    }
}
