<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GNarrativeGroupExtractorTrait
{
    use JsonDecoderTrait;

    public function getNarrativeGroup(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-narrative-group')->each(function (Crawler $crawler) use (&$content): void {
            $out = [];

            $crawler->filterXPath('.//g-article')->each(function (Crawler $article) use (&$out): void {
                $headline = $article->attr('headline');
                $byline = $article->attr('byline');
                $body = $article->attr('body');

                if (! empty($headline)) {
                    $out[] = sprintf('<h1>%s</h1>', $headline);
                }

                if (! empty($byline)) {
                    $out[] = sprintf('<p>%s<br /></p>', $byline);
                }

                if (! empty($body)) {
                    $out[] = $body;
                }
            });

            $crawler->filterXPath('.//g-illustration')->each(function (Crawler $illustration) use (&$out): void {
                $signIntro = $illustration->attr('sign-intro');
                $signName = $illustration->attr('sign-name');
                $signLink = $illustration->attr('sign-link-href');

                if (! empty($signIntro)) {
                    $out[] = sprintf('<p class="illustration-credit">%s</p>', $signIntro);
                }

                if (! empty($signName)) {
                    if (! empty($signLink)) {
                        $out[] = sprintf('<a href="%s">%s</a>', $signLink, $signName);
                    } else {
                        $out[] = sprintf('<p>%s</p>', $signName);
                    }
                }
            });

            $content .= collect($out)->implode("\n");
        });

        return $content;
    }
}
