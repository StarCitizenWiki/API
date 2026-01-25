<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GHeaderExtractorTrait
{
    public function getHeader(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-header')->each(function (Crawler $crawler) use (&$content): void {
            $titleSlot = $crawler->filterXPath('//template[@slot="title"]');
            if ($titleSlot->count() > 0) {
                $titleContent = $titleSlot->html();
                $content .= sprintf('<h1>%s</h1>', $titleContent);
            }

            $contentSlot = $crawler->filterXPath('//template[@slot="content"]');
            if ($contentSlot->count() > 0) {
                $content .= $contentSlot->html();
            }
        });

        return $content;
    }
}
