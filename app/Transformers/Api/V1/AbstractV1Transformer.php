<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1;

use League\Fractal\TransformerAbstract;

/**
 * Contains static routes fragments
 * Removes to necessity to call app('api.url')->...
 */
abstract class AbstractV1Transformer extends TransformerAbstract
{
    public const COMM_LINKS_SHOW = '/api/comm-links/%d';
    public const COMM_LINKS_SERIES_SHOW = '/api/comm-links/series/%s';
    public const COMM_LINKS_CHANNELS_SHOW = '/api/comm-links/channels/%s';
    public const COMM_LINKS_CATEGORIES_SHOW = '/api/comm-links/categories/%s';

    public const VEHICLES_SHIPS_SHOW = '/api/ships/%s';
    public const VEHICLES_GROUND_VEHICLES_SHOW = '/api/vehicles/%s';

    public function includeAllAvailableIncludes(): void
    {
        $this->setDefaultIncludes($this->getAvailableIncludes());
    }

    /**
     * Formats the fragment and returns an absolute api url
     *
     * @param string $fragment
     * @param mixed  ...$routeKey
     *
     * @return string
     */
    protected function makeApiUrl(string $fragment, ...$routeKey): string
    {
        return sprintf('%s' . $fragment, config('app.url'), ...$routeKey);
    }
}
