<?php

declare(strict_types=1);
/*
 * Copyright (c) 2020
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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
