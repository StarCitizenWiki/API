<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait GTumbrilFeaturesExtractorTrait
{
    use JsonDecoderTrait;

    public function getTumbrilFeatures(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-tumbril-features')->each(function (Crawler $crawler) use (&$content): void {
            $features = $crawler->attr(':features');

            if ($features === null) {
                return;
            }

            $features = preg_replace('/([a-z0-9]+):\s/', '"$1": ', $features) ?? $features;
            $features = $this->decodeJsonString(str_replace("'", '"', $features));

            if ($features === null) {
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
