<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractBaseElement
{
    protected function cleanText(string $string): string
    {
        return trim(preg_replace('/\R/', '', $string) ?? $string);
    }

    protected function isSpecialPage(Crawler $commLink): bool
    {
        return $commLink->filter('#layout-system')->count() === 1;
    }

    protected function isSubscriberPage(Crawler $commLink): bool
    {
        return $commLink->filter('div#subscribers')->count() === 1;
    }
}
