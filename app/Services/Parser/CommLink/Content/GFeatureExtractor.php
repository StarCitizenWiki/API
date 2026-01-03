<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use Symfony\Component\DomCrawler\Crawler;

final class GFeatureExtractor implements ContentExtractorInterface
{
    use GIntroductionExtractorTrait;

    private Crawler $page;

    public function __construct(Crawler $page)
    {
        $this->page = $page;
    }

    public function getContent(): string
    {
        $content = $this->getIntroduction($this->page);

        $extract = function (Crawler $crawler) use (&$content): void {
            $this->getGFeaturesIntro($crawler, $content);

            $crawler->filterXPath('//template')->each(function (Crawler $crawler) use (&$content): void {
                $slot = $crawler->attr('slot');
                switch ($slot) {
                    case 'title':
                        $content .= sprintf('<h2>%s</h2>', $crawler->text());
                        break;

                    case 'subtitle':
                        $content .= sprintf('<h3>%s</h3>', $crawler->text());
                        break;

                    case 'body':
                        $content .= $crawler->html();
                        break;

                    default:
                        break;
                }
            });
        };

        $this->page->filterXPath('//g-feature')->each($extract);

        return $content;
    }

    public static function getFilter(): string
    {
        return '//g-feature';
    }

    public static function canParse(Crawler $page): array
    {
        $count = $page->filterXPath(self::getFilter())->count();

        return [
            $count > 0,
            $count,
        ];
    }

    private function getGFeaturesIntro(Crawler $crawler, string &$content): void
    {
        if ($crawler->attr(':is-header-declared') === 'true') {
            $crawler->filterXPath('//template')->each(function (Crawler $crawler) use (&$content): void {
                if ($crawler->attr('slot') === 'title') {
                    $content .= sprintf('<h1>%s</h1>', $crawler->text());
                }
                if ($crawler->attr('slot') === 'subtitle') {
                    $content .= sprintf('<h2>%s</h2>', $crawler->text());
                }
                if ($crawler->attr('slot') === 'introduction') {
                    $content .= $crawler->html();
                }
            });
        }
    }
}
