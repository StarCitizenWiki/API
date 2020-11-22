<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Base Methods for Elements.
 */
abstract class AbstractBaseElement
{
    /**
     * Removes all new lines and trims the string.
     *
     * @param string $string
     *
     * @return string cleaned text
     */
    protected function cleanText(string $string): string
    {
        return trim(preg_replace('/\R/', '', $string));
    }

    /**
     * Checks if Comm-Link Page is a Ship Page
     * Ship Pages are wrapped in a '#layout-system' Div.
     *
     * @param Crawler $commLink
     *
     * @return bool
     */
    protected function isSpecialPage(Crawler $commLink): bool
    {
        return 1 === $commLink->filter('#layout-system')->count();
    }

    /**
     * Checks if Comm-Link Page is a Subscriber Article.
     *
     * @param Crawler $commLink
     *
     * @return bool
     */
    protected function isSubscriberPage(Crawler $commLink): bool
    {
        return 1 === $commLink->filter('div#subscribers')->count();
    }
}
