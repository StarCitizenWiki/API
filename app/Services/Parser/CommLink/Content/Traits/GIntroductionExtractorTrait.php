<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use JsonException;
use Symfony\Component\DomCrawler\Crawler;

trait GIntroductionExtractorTrait
{

    /**
     * Extract <g-introduction> content
     *
     * @param Crawler $page
     * @return string
     */
    public function getIntroduction(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-introduction')->each(
            function (Crawler $crawler) use (&$content) {
                $info = $crawler->attr(':info');
                if ($info === null) {
                    return;
                }

                try {
                    $info = json_decode($info, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    return;
                }

                $out = [];
                if (isset($info['title']) && !empty($info['title'])) {
                    $out[] = sprintf('<h1>%s</h1>', $info['title']);
                }
                if (isset($info['subtitle']) && !empty($info['subtitle'])) {
                    $out[] = sprintf('<h2>%s</h2>', $info['subtitle']);
                }
                if (isset($info['contents']) && !empty($info['contents'])) {
                    $out[] = collect($info['contents'])->implode("\n");
                }

                $content .= collect($out)->implode("\n");
            }
        );

        return $content;
    }
}
