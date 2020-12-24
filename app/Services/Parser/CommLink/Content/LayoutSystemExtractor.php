<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use Symfony\Component\DomCrawler\Crawler;

final class LayoutSystemExtractor implements ContentExtractorInterface
{
    private Crawler $page;

    public function __construct(Crawler $page)
    {
        $this->page = $page;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        $content = '';

        $this->page->filter(self::getFilter())->each(
            function (Crawler $crawler) use (&$content) {
                $content .= ltrim($crawler->html());
            }
        );

        return $content;
    }

    /**
     * @inheritDoc
     */
    public static function getFilter(): string
    {
        return '#layout-system';
    }

    /**
     * @inheritDoc
     */
    public static function canParse(Crawler $page): array
    {
        $count = $page->filter(self::getFilter())->count();

        return [
            $count > 0,
            $count,
        ];
    }
}
