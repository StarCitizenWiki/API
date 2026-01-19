<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GGridExtractorTrait
{
    public function getGrid(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-grid')->each(function (Crawler $crawler) use (&$content): void {
            $cards = $crawler->attr(':cards');
            if ($cards === null) {
                return;
            }

            try {
                $cards = json_decode($cards, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return;
            }

            $out = [];

            foreach ($cards as $card) {
                $card = $card['content'];

                if (! empty($card['title'] ?? null)) {
                    $out[] = sprintf('<h1>%s</h1>', $card['title']);
                }

                if (! empty($card['summary'] ?? null)) {
                    $out[] = sprintf('<p>%s</p>', $card['summary']);
                }

                if (isset($card['description'])) {
                    $out[] = sprintf('<p>%s</p>', $card['description']);
                }
            }

            $content .= collect($out)->implode("\n");

            $crawler->filter('pre')->each(static function (Crawler $crawler) {
                $node = $crawler->getNode(0);
                if ($node !== null) {
                    $node->parentNode?->removeChild($node);
                }

                return $crawler;
            });
        });

        return $content;
    }
}
