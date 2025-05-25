<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use Symfony\Component\DomCrawler\Crawler;

class ContentExtractorFactory
{
    /**
     * @return ContentExtractorInterface|null The parser matching the most elements
     */
    public static function getParserFromCrawler(Crawler $crawler): ?ContentExtractorInterface
    {
        return collect(
            [
                DefaultExtractor::class,
                LayoutSystemExtractor::class,
                VueArticleExtractor::class,
                GFeatureExtractor::class,
                AlexandriaExtractor::class,
            ]
        )
            ->map(
                function (string $parser) use ($crawler) {
                    return array_merge(call_user_func([$parser, 'canParse'], $crawler), [$parser]);
                }
            )
            ->filter(fn (array $res) => $res[0] === true)
            ->sortByDesc('1') // Sort by count of matched items
            ->map(
                function (array $result) use ($crawler) {
                    return new $result[2]($crawler);
                }
            )
            ->first();
    }
}
