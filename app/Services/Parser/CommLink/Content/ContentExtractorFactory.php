<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use Symfony\Component\DomCrawler\Crawler;

class ContentExtractorFactory
{
    public static function getParserFromCrawler(Crawler $crawler): ?ContentExtractorInterface
    {
        return collect([
            DefaultExtractor::class,
            LayoutSystemExtractor::class,
            VueArticleExtractor::class,
            GFeatureExtractor::class,
            AlexandriaExtractor::class,
        ])
            ->map(function (string $parser) use ($crawler): array {
                return array_merge(call_user_func([$parser, 'canParse'], $crawler), [$parser]);
            })
            ->filter(static fn (array $result) => $result[0] === true)
            ->sortByDesc('1')
            ->map(function (array $result) use ($crawler): ContentExtractorInterface {
                return new $result[2]($crawler);
            })
            ->first();
    }
}
