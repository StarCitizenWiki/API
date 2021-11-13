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

    /**
     * {@inheritDoc}
     */
    public function getContent(): string
    {
        $content = $this->getIntroduction($this->page);

        $this->page->filterXPath('//g-features')->each(
            function (Crawler $crawler) use (&$content) {
                $this->getGFeaturesIntro($crawler, $content);

                $crawler->filterXPath(self::getFilter())->each(function (Crawler $crawler) use (&$content) {
                    $crawler->filterXPath('//template')->each(function (Crawler $crawler) use (&$content) {
                        $slot = $crawler->attr('slot');
                        switch ($slot) {
                            case 'title':
                                $content .= sprintf('<h2>%s</h2>', $crawler->text());
                                break;

                            case 'body':
                                $content .= $crawler->html();
                                break;

                            default:
                                break;
                        }
                    });
                });
            }
        );

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public static function getFilter(): string
    {
        return '//g-feature';
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

    /**
     * <g-features> Intro
     *
     * @param Crawler $crawler
     * @param string $content
     */
    private function getGFeaturesIntro(Crawler $crawler, string &$content): void
    {
        if ($crawler->attr(':is-header-declared') === 'true') {
            $crawler->filterXPath('//template')->each(function (Crawler $crawler) use (&$content) {
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
