<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\GExploreExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GGridExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GTumbrilFeaturesExtractorrait;
use Symfony\Component\DomCrawler\Crawler;

final class LayoutSystemExtractor implements ContentExtractorInterface
{
    use GIntroductionExtractorTrait;
    use GTumbrilFeaturesExtractorrait;
    use GExploreExtractorTrait;
    use GGridExtractorTrait;

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
        $content .= $this->getTumbrilFeatures($this->page);
        $content .= $this->getExplore($this->page);
        $content .= $this->getGrid($this->page);

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
        return '#layout-system';
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
