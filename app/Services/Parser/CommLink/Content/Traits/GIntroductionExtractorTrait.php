<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GIntroductionExtractorTrait
{
    public function getIntroduction(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-introduction')->each(function (Crawler $crawler) use (&$content): void {
            $info = $crawler->attr(':info');
            if ($info === null) {
                return;
            }

            try {
                $info = json_decode($info, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                return;
            }

            $out = [];
            if (! empty($info['title'] ?? null)) {
                $out[] = sprintf('<h1>%s</h1>', $info['title']);
            }
            if (! empty($info['subtitle'] ?? null)) {
                $out[] = sprintf('<h2>%s</h2>', $info['subtitle']);
            }
            if (! empty($info['contents'] ?? null)) {
                $out[] = collect($info['contents'])->implode("\n");
            }

            $content .= collect($out)->implode("\n");
        });

        return $content;
    }
}
