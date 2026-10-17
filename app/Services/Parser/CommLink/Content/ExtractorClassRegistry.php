<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

final class ExtractorClassRegistry
{
    /**
     * @return array<int, class-string<ContentExtractorInterface>>
     */
    public static function getExtractors(): array
    {
        return [
            UniversalContentExtractor::class,
            DefaultExtractor::class,
            LayoutSystemExtractor::class,
            VueArticleExtractor::class,
            AlexandriaExtractor::class,
        ];
    }
}
