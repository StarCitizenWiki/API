<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GAuthorExtractorTrait
{
    public function getAuthor(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-author')->each(function (Crawler $crawler) use (&$content): void {
            $authorName = $crawler->attr('author-name');
            $authorDesc = $crawler->attr('author-desc');
            $authorLink = $crawler->attr('author-link');

            if ($authorName === null) {
                return;
            }

            $out = [];

            if ($authorName !== null) {
                $out[] = sprintf('<h3>%s</h3>', $authorName);
            }

            if ($authorDesc !== null) {
                $out[] = sprintf('<p>%s</p>', $authorDesc);
            }

            if ($authorLink !== null && ! empty($out)) {
                $out[] = sprintf('<a href="%s">Source</a>', $authorLink);
            }

            $content .= collect($out)->implode("\n");
        });

        return $content;
    }
}
