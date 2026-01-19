<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GExploreExtractorTrait
{
    public function getExplore(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-explore')->each(function (Crawler $crawler) use (&$content): void {
            $explore = $crawler->attr(':decks');
            if ($explore === null) {
                return;
            }

            try {
                $explore = json_decode($explore, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
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
