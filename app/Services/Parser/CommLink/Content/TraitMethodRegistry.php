<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

final class TraitMethodRegistry
{
    /**
     * @var array<string, string>
     */
    private static array $methodMap = [
        'g-introduction' => 'getIntroduction',
        'g-banner-advanced' => 'getBannerAdvancedContent',
        'g-feature' => 'getGFeatureContent',
        'g-explore' => 'getExplore',
        'g-grid' => 'getGrid',
        'g-skus' => 'getSkusContent',
        'g-tumbril-features' => 'getTumbrilFeatures',
        'g-narrative-group' => 'getNarrativeGroup',
        'g-illustration' => 'getIllustration',
        'g-author' => 'getAuthor',
        'g-faq' => 'getFaq',
        'g-header' => 'getHeader',
        'g-article' => 'getVueArticleContent',
        'g-platform-client-component' => 'getAlexandriaComponents',
    ];

    public static function getMethod(string $elementName): ?string
    {
        return self::$methodMap[strtolower(trim($elementName))] ?? null;
    }
}
