<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GFeatureExtractorTrait
{
    /**
     * Extracts content from g-feature elements.
     */
    public function getGFeatureContent(Crawler $page): string
    {
        $content = '';

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

        $page->filterXPath('//g-feature')->each($extract);

        return $content;
    }

    /**
     * Extracts the introduction from a g-feature element if it is declared as a header.
     */
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
