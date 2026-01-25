<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\AlexandriaComponentExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GBannerAdvancedExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GFeatureExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

final class AlexandriaExtractor implements ContentExtractorInterface
{
    use AlexandriaComponentExtractorTrait;
    use GBannerAdvancedExtractorTrait;
    use GFeatureExtractorTrait;
    use GIntroductionExtractorTrait;

    public function __construct(public Crawler $page) {}

    public function getContent(bool $withIntroduction = true): string
    {
        $content = '';

        if ($withIntroduction) {
            $content = $this->getIntroduction($this->page);
        }

        $content .= $this->getAlexandriaComponents($this->page);
        $content .= $this->getBannerAdvancedContent($this->page);
        $content .= $this->getGFeatureContent($this->page);

        $this->page->filterXPath('//g-navigation-sales')->each(function (Crawler $crawler) use (&$content): void {
            $navigationAttr = $crawler->attr(':navigation');
            if (! empty($navigationAttr)) {
                $navigationJson = json_decode($navigationAttr, true);
                if (isset($navigationJson['items']) && is_array($navigationJson['items'])) {
                    $content .= '<ul>';
                    foreach ($navigationJson['items'] as $item) {
                        if (isset($item['label'])) {
                            $content .= '<li>'.$item['label'].'</li>';
                        }
                    }
                    $content .= '</ul>';
                }
            }
        });

        return $content;
    }

    public static function getFilter(): string
    {
        return 'g-platform-client-component, g-banner-advanced, g-navigation-sales, g-feature';
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
