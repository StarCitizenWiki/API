<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GSkusExtractorTrait
{
    /**
     * Extract <g-banner-advanced> and <g-skus> content
     *
     * @param Crawler $page
     *
     * @return string
     */
    public function getSkusContent(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-skus')->each(
            function (Crawler $crawler) use (&$content) {
                $textContent = $crawler->attr(':properties');

                if ($textContent === null) {
                    return;
                }

                try {
                    $textContent = json_decode($textContent, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return;
                }

                if (!isset($textContent['blocks'])) {
                    return;
                }

                $out = [];

                foreach ($textContent['blocks'] as $block) {
                    if (!isset($block['properties']) || ($block['type'] ?? '') !== 'text') {
                        continue;
                    }

                    $block = $block['properties'];

                    if (!empty($block['title'])) {
                        $out[] = sprintf('<h1>%s</h1>', $block['title']);
                    }

                    if (!empty($block['subtitle'])) {
                        $out[] = sprintf('<h2>%s</h2>', $block['subtitle']);
                    }

                    if (!empty($block['content'])) {
                        $out[] = $block['content'];
                    }
                }

                $content .= collect($out)->implode("\n");
            }
        );

        return $content;
    }
}
