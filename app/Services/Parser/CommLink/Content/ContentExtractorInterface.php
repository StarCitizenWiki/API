<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use Symfony\Component\DomCrawler\Crawler;

interface ContentExtractorInterface
{
    /**
     * ContentParserInterface constructor.
     *
     * @param Crawler $page The page to parse
     */
    public function __construct(Crawler $page);

    /**
     * The raw HTML text content
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * The filter used by the crawler, CSS Selector of XPath
     *
     * @return string
     */
    public static function getFilter(): string;

    /**
     * Check if a parser can parse the page
     * Usually checks if the element filter exists in the page
     *
     * @param Crawler $page
     *
     * @return array Two element array containing a bool on position 0 and the number of matched elements on position 1
     */
    public static function canParse(Crawler $page): array;
}
