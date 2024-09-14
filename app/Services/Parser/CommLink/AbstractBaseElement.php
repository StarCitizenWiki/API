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
     */
    protected function isSpecialPage(Crawler $commLink): bool
    {
        return $commLink->filter('#layout-system')->count() === 1;
    }

    /**
     * Checks if Comm-Link Page is a Subscriber Article.
     */
    protected function isSubscriberPage(Crawler $commLink): bool
    {
        return $commLink->filter('div#subscribers')->count() === 1;
    }
}
