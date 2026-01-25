<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GSkusExtractorTrait
{
    use JsonDecoderTrait;

    public function getSkusContent(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-skus')->each(function (Crawler $crawler) use (&$content): void {
            $textContent = $this->decodeJsonAttribute($crawler, ':properties');

            if ($textContent === null) {
                return;
            }

            if (! isset($textContent['blocks'])) {
                return;
            }

            $out = [];

            foreach ($textContent['blocks'] as $block) {
                if (! isset($block['properties']) || ($block['type'] ?? '') !== 'text') {
                    continue;
                }

                $block = $block['properties'];

                if (! empty($block['title'])) {
                    $out[] = sprintf('<h1>%s</h1>', $block['title']);
                }

                if (! empty($block['subtitle'])) {
                    $out[] = sprintf('<h2>%s</h2>', $block['subtitle']);
                }

                if (! empty($block['content'])) {
                    $out[] = $block['content'];
                }
            }

            $content .= collect($out)->implode("\n");
        });

        return $content;
    }
}
