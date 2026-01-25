<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GBannerAdvancedExtractorTrait
{
    use JsonDecoderTrait;

    public function getBannerAdvancedContent(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-banner-advanced')->each(function (Crawler $crawler) use (&$content): void {
            $textContent = $this->decodeJsonAttribute($crawler, ':content');

            if ($textContent === null) {
                return;
            }

            if (! isset($textContent['text'])) {
                return;
            }

            $textContent = $textContent['text'];

            $out = [];

            if (! empty($textContent['overline'])) {
                $out[] = sprintf('<h1>%s</h1>', $textContent['overline']);
            }

            if (! empty($textContent['title'])) {
                $out[] = sprintf('<h1>%s</h1>', $textContent['title']);
            }

            if (! empty($textContent['subtitle'])) {
                $out[] = sprintf('<h2>%s</h2>', $textContent['subtitle']);
            }

            if (! empty($textContent['paragraph'])) {
                $out[] = sprintf('<p>%s</p>', $textContent['paragraph']);
            }

            $content .= collect($out)->implode("\n");
        });

        return $content;
    }
}
