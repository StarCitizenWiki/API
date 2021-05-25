<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GExploreExtractorTrait
{

    /**
     * Extract <g-introduction> content
     *
     * @param Crawler $page
     * @return string
     */
    public function getExplore(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-explore')->each(
            function (Crawler $crawler) use (&$content) {
                $explore = $crawler->attr(':decks');
                if ($explore === null) {
                    return;
                }

                try {
                    $explore = json_decode($explore, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return;
                }
                $out = [];

                foreach ($explore as $side) {
                    if (isset($side['title']) && !empty($side['title'])) {
                        $out[] = sprintf('<h1>%s</h1>', $side['title']);
                    }

                    if (isset($side['spots']) && is_array($side['spots'])) {
                        foreach ($side['spots'] as $spot) {
                            if (isset($spot['boxTitle']) && isset($spot['boxContent'])) {
                                $out[] = sprintf('<h2>%s</h2>', $spot['boxTitle']);
                                $out[] = sprintf('<p>%s</p>', $spot['boxContent']);
                            }
                        }
                    }
                }

                $content .= collect($out)->implode("\n");
            }
        );

        return $content;
    }
}
