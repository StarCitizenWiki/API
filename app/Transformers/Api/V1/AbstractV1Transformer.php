<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1;

use App\Transformers\Api\LocalizableTransformerInterface;
use League\Fractal\TransformerAbstract;
use RuntimeException;
use Throwable;

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

    public const VEHICLES_SHOW = '/api/vehicles/%s';

    public const STARMAP_STARSYSTEM_SHOW = '/api/starmap/starsystems/%s';
    public const STARMAP_CELESTIAL_OBJECTS_SHOW = '/api/starmap/celestial-objects/%s';

    public const GALACTAPEDIA_ARTICLE_SHOW = '/api/galactapedia/%s';

    public const UNPACKED_CHAR_ARMOR_SHOW = '/api/char/armor/%s';

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

    /**
     * Instantiates a new transformer and sets the $bases locale if both transformers implement
     * LocalizableTransformerInterface
     *
     * @param string                   $class
     * @param TransformerAbstract|null $base
     *
     * @return TransformerAbstract
     * @throws Throwable
     */
    protected function makeTransformer(string $class, ?TransformerAbstract $base = null): TransformerAbstract
    {
        throw_if(!class_exists($class), new RuntimeException("Class $class not found."));

        $transformer = app($class);

        //phpcs:ignore PSR12.ControlStructures.ControlStructureSpacing.FirstExpressionLine
        if ($base !== null
            && $transformer instanceof LocalizableTransformerInterface
            && $base instanceof LocalizableTransformerInterface
            && $base->getLocale() !== null
        ) {
            $transformer->setLocale($base->getLocale());
        }

        return $transformer;
    }
}
