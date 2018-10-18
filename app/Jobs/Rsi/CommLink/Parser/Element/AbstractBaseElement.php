<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 11.09.2018
 * Time: 17:49
 */

namespace App\Jobs\Rsi\CommLink\Parser\Element;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Base Methods for Elements
 */
abstract class AbstractBaseElement
{
    /**
     * Removes all new lines and trims the string
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
     * Ship Pages are wrapped in a '#layout-system' Div
     *
     * @param \Symfony\Component\DomCrawler\Crawler $commLink
     *
     * @return bool
     */
    protected function isSpecialPage(Crawler $commLink): bool
    {
        return $commLink->filter('#layout-system')->count() === 1;
    }

    /**
     * Checks if Comm-Link Page is a Subscriber Article
     *
     * @param \Symfony\Component\DomCrawler\Crawler $commLink
     *
     * @return bool
     */
    protected function isSubscriberPage(Crawler $commLink): bool
    {
        return $commLink->filter('div#subscribers')->count() === 1;
    }
}
