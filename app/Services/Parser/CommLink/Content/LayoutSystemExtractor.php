<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\GBannerAdvancedExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GExploreExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GGridExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GSkusExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GTumbrilFeaturesExtractorrait;
use Symfony\Component\DomCrawler\Crawler;

final class LayoutSystemExtractor implements ContentExtractorInterface
{
    use GBannerAdvancedExtractorTrait;
    use GExploreExtractorTrait;
    use GGridExtractorTrait;
    use GIntroductionExtractorTrait;
    use GSkusExtractorTrait;
    use GTumbrilFeaturesExtractorrait;

    private Crawler $page;

    public function __construct(Crawler $page)
    {
        $this->page = $page;
    }

    public function getContent(): string
    {
        $content = $this->getIntroduction($this->page);
        $content .= $this->getTumbrilFeatures($this->page);
        $content .= $this->getExplore($this->page);
        $content .= $this->getGrid($this->page);
        $content .= $this->getBannerAdvancedContent($this->page);
        $content .= $this->getSkusContent($this->page);

        $vue = new VueArticleExtractor($this->page);
        $content .= $vue->getContent(false);

        $this->page->filter(self::getFilter())->each(function (Crawler $crawler) use (&$content): void {
            $content .= ltrim($crawler->html() ?? '');
        });

        return $content;
    }

    public static function getFilter(): string
    {
        return '#layout-system';
    }

    public static function canParse(Crawler $page): array
    {
        $count = $page->filter(self::getFilter())->count();

        return [
            $count > 0,
            $count + 10,
        ];
    }
}
