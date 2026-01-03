<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\GBannerAdvancedExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

final class AlexandriaExtractor implements ContentExtractorInterface
{
    use GBannerAdvancedExtractorTrait;
    use GIntroductionExtractorTrait;

    private Crawler $page;

    public function __construct(Crawler $page)
    {
        $this->page = $page;
    }

    public function getContent(bool $withIntroduction = true): string
    {
        $content = '';

        if ($withIntroduction) {
            $content = $this->getIntroduction($this->page);
        }

        $this->page->filterXPath('//g-platform-client-component')->each(function (Crawler $crawler) use (&$content): void {
            $properties = $crawler->attr(':properties');
            if (! empty($properties)) {
                $content .= $this->extractContentFromProperties($properties);
            }
        });

        $content .= $this->getBannerAdvancedContent($this->page);
        $content .= (new GFeatureExtractor($this->page))->getContent();

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

    private function extractContentFromProperties(string $properties): string
    {
        $content = '';
        $json = json_decode($properties, true);

        if (! $json) {
            return $content;
        }

        if (($json['componentId'] ?? null) === 'Text') {
            if (isset($json['componentProps']['text'])) {
                $content .= '<p>'.$json['componentProps']['text'].'</p>';
            }
            if (isset($json['componentProps']['title'])) {
                $content .= '<h2>'.$json['componentProps']['title'].'</h2>';
            }
        }

        if (isset($json['componentProps']['text'])) {
            $content .= '<p>'.$json['componentProps']['text'].'</p>';
        }
        if (isset($json['componentProps']['title'])) {
            $content .= '<h2>'.$json['componentProps']['title'].'</h2>';
        }
        if (isset($json['componentProps']['description'])) {
            $content .= '<p>'.$json['componentProps']['description'].'</p>';
        }

        return $content;
    }

    public static function getFilter(): string
    {
        return 'g-platform-client-component, g-banner-advanced, g-navigation-sales';
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
