<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GGridExtractorTrait
{
    /**
     * Extract <g-grid> content
     *
     * @param Crawler $page
     * @return string
     */
    public function getGrid(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-grid')->each(
            function (Crawler $crawler) use (&$content) {
                $cards = $crawler->attr(':cards');
                if ($cards === null) {
                    return;
                }

                try {
                    $cards = json_decode($cards, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return;
                }
                $out = [];

                foreach ($cards as $card) {
                    $card = $card['content'];

                    if (isset($card['title']) && !empty($card['title'])) {
                        $out[] = sprintf('<h1>%s</h1>', $card['title']);
                    }

                    if (!empty($card['summary'])) {
                        $out[] = sprintf('<p>%s</p>', $card['summary']);
                    }

                    $out[] = sprintf('<p>%s</p>', $card['description']);
                }

                $content .= collect($out)->implode("\n");

                $crawler->filter('pre')->each(static function (Crawler $crawler) {
                    $node = $crawler->getNode(0);
                    if ($node !== null) {
                        $node->parentNode->removeChild($node);
                    }

                    return $crawler;
                });
            }
        );

        return $content;
    }
}
