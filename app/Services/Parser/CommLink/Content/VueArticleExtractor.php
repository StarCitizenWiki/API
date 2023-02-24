<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

final class VueArticleExtractor implements ContentExtractorInterface
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

        $this->page->filterXPath(self::getFilter())->each(
            function (Crawler $crawler) use (&$content) {
                $data = [];
                $data[] = $crawler->attr('headline');
                $data[] = $crawler->attr('byline');
                $data[] = $crawler->attr('body');

                $content .= ltrim(
                    collect($data)->filter(
                        function ($data) {
                            return null !== $data;
                        }
                    )->implode('<br>')
                );
            }
        );

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public static function getFilter(): string
    {
        return '//g-article';
    }

    /**
     * {@inheritDoc}
     */
    public static function canParse(Crawler $page): array
    {
        $count = $page->filterXPath(self::getFilter())->count();

        return [
            $count > 0,
            $count,
        ];
    }
}
