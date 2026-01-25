<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

final class DefaultExtractor implements ContentExtractorInterface
{
    use GIntroductionExtractorTrait;

    public function __construct(public Crawler $page) {}

    public function getContent(): string
    {
        $content = $this->getIntroduction($this->page);

        $this->page->filter(self::getFilter())->each(function (Crawler $crawler) use (&$content): void {
            $content .= ltrim($crawler->html() ?? '');
        });

        return $content;
    }

    public static function getFilter(): string
    {
        return '.segment';
    }

    public static function canParse(Crawler $page): array
    {
        $count = $page->filter(self::getFilter())->count();

        return [
            $count > 0,
            $count,
        ];
    }
}
