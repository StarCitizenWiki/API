<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\AlexandriaComponentExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GBannerAdvancedExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GExploreExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GFeatureExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GGridExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GIllustrationExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GNarrativeGroupExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GSkusExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GTumbrilFeaturesExtractorTrait;

final class ExtractorClassRegistry
{
    /**
     * @var array<string, class-string>
     */
    private static array $extractors = [
        'g-introduction' => GIntroductionExtractorTrait::class,
        'g-banner-advanced' => GBannerAdvancedExtractorTrait::class,
        'g-feature' => GFeatureExtractorTrait::class,
        'g-explore' => GExploreExtractorTrait::class,
        'g-grid' => GGridExtractorTrait::class,
        'g-skus' => GSkusExtractorTrait::class,
        'g-tumbril-features' => GTumbrilFeaturesExtractorTrait::class,
        'g-article' => VueArticleExtractor::class,
        'g-narrative-group' => GNarrativeGroupExtractorTrait::class,
        'g-illustration' => GIllustrationExtractorTrait::class,
        'g-author' => GAuthorExtractorTrait::class,
        'g-faq' => GFaqExtractorTrait::class,
        'g-header' => GHeaderExtractorTrait::class,
        'g-platform-client-component' => AlexandriaComponentExtractorTrait::class,
        'text' => AlexandriaComponentExtractorTrait::class,
        'image' => AlexandriaComponentExtractorTrait::class,
        'video' => AlexandriaComponentExtractorTrait::class,
        'quote' => AlexandriaComponentExtractorTrait::class,
        'gallery' => AlexandriaComponentExtractorTrait::class,
        'button' => AlexandriaComponentExtractorTrait::class,
        'calltoaction' => AlexandriaComponentExtractorTrait::class,
    ];

    public static function getExtractor(string $elementName): ?string
    {
        return self::$extractors[strtolower(trim($elementName))] ?? null;
    }

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
