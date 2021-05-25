<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GTumbrilFeaturesExtractorrait
{

    /**
     * TODO: Generalize this into a generic g-*-features
     * Extract <g-tumbril-features> content
     *
     * @param Crawler $page
     *
     * @return string
     */
    public function getTumbrilFeatures(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-tumbril-features')->each(
            function (Crawler $crawler) use (&$content) {
                $features = $crawler->attr(':features');

                if ($features === null) {
                    return;
                }

                $features = preg_replace('/([a-z0-9]+):\s/', '"$1": ', $features);

                try {
                    $features = json_decode(str_replace('\'', '"', $features), true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return;
                }

                $out = [];
                foreach ($features as $feature) {
                    $string = '';

                    if (isset($feature['title']) && !empty($feature['title'])) {
                        $string .= sprintf('<h1>%s</h1>', $feature['title']);
                    }

                    if (isset($feature['content']) && !empty($feature['content'])) {
                        $string .= sprintf('<p>%s</p>', $feature['content']);
                    }

                    $out[] = $string;
                }

                $content .= collect($out)->implode("\n");
            }
        );

        return $content;
    }
}
