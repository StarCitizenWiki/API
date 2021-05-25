<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

final class DefaultExtractor implements ContentExtractorInterface
{
    use GIntroductionExtractorTrait;

    private Crawler $page;

    public function __construct(Crawler $page)
    {
        $this->page = $page;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent(): string
    {
        $content = $this->getIntroduction($this->page);

        $this->page->filter(self::getFilter())->each(
            function (Crawler $crawler) use (&$content) {
                $content .= ltrim($crawler->html());
            }
        );

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public static function getFilter(): string
    {
        return '.segment';
    }

    /**
     * {@inheritDoc}
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
