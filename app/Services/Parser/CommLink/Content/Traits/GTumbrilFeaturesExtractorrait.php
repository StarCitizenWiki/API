<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GTumbrilFeaturesExtractorrait
{
    public function getTumbrilFeatures(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-tumbril-features')->each(function (Crawler $crawler) use (&$content): void {
            $features = $crawler->attr(':features');

            if ($features === null) {
                return;
            }

            $features = preg_replace('/([a-z0-9]+):\s/', '"$1": ', $features) ?? $features;

            try {
                $features = json_decode(str_replace("'", '"', $features), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return;
            }

            $out = [];
            foreach ($features as $feature) {
                $string = '';

                if (! empty($feature['title'] ?? null)) {
                    $string .= sprintf('<h1>%s</h1>', $feature['title']);
                }

                if (! empty($feature['content'] ?? null)) {
                    $string .= sprintf('<p>%s</p>', $feature['content']);
                }

                $out[] = $string;
            }

            $content .= collect($out)->implode("\n");
        });

        return $content;
    }
}
