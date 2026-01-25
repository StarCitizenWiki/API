<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GExploreExtractorTrait
{
    use JsonDecoderTrait;

    public function getExplore(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-explore')->each(function (Crawler $crawler) use (&$content): void {
            $explore = $this->decodeJsonAttribute($crawler, ':decks');
            if ($explore === null) {
                return;
            }

            $out = [];

            foreach ($explore as $side) {
                if (! empty($side['title'] ?? null)) {
                    $out[] = sprintf('<h1>%s</h1>', $side['title']);
                }

                if (isset($side['spots']) && is_array($side['spots'])) {
                    foreach ($side['spots'] as $spot) {
                        if (isset($spot['boxTitle'], $spot['boxContent'])) {
                            $out[] = sprintf('<h2>%s</h2>', $spot['boxTitle']);
                            $out[] = sprintf('<p>%s</p>', $spot['boxContent']);
                        }
                    }
                }
            }

            $content .= collect($out)->implode("\n");
        });

        return $content;
    }
}
