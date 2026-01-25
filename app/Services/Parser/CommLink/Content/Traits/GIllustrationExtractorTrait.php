<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GIllustrationExtractorTrait
{
    public function getIllustration(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-illustration')->each(function (Crawler $crawler) use (&$content): void {
            $signIntro = $crawler->attr('sign-intro');
            $signName = $crawler->attr('sign-name');
            $signLinkHref = $crawler->attr('sign-link-href');

            $out = [];

            if ($signIntro !== null && $signIntro !== '') {
                $out[] = sprintf('<p class="illustration-credit">%s</p>', $signIntro);
            }

            if ($signName !== null && $signName !== '') {
                if ($signLinkHref !== null && $signLinkHref !== '') {
                    $out[] = sprintf('<a href="%s">%s</a>', $signLinkHref, $signName);
                } else {
                    $out[] = $signName;
                }
            }

            $content .= collect($out)->implode('');
        });

        return $content;
    }
}
