<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

use App\Services\Parser\CommLink\Content\Traits\AlexandriaComponentExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GAuthorExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GBannerAdvancedExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GExploreExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GFaqExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GGridExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GHeaderExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GIllustrationExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GIntroductionExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GNarrativeGroupExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GSkusExtractorTrait;
use App\Services\Parser\CommLink\Content\Traits\GTumbrilFeaturesExtractorTrait;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

final class UniversalContentExtractor implements ContentExtractorInterface
{
    use AlexandriaComponentExtractorTrait;
    use GAuthorExtractorTrait;
    use GBannerAdvancedExtractorTrait;
    use GExploreExtractorTrait;
    use GFaqExtractorTrait;
    use GGridExtractorTrait;
    use GHeaderExtractorTrait;
    use GIllustrationExtractorTrait;
    use GIntroductionExtractorTrait;
    use GNarrativeGroupExtractorTrait;
    use GSkusExtractorTrait;
    use GTumbrilFeaturesExtractorTrait;

    public function __construct(public Crawler $page) {}

    public function getContent(): string
    {
        $content = '';

        $this->page->filterXPath('//*[starts-with(local-name(), "g-")]')->each(function (Crawler $crawler) use (&$content): void {
            $elementName = $crawler->nodeName();
            $method = TraitMethodRegistry::getMethod($elementName);

            if ($method !== null) {
                $content .= $this->extractWithTrait($crawler, $elementName);
            } else {
                $text = $crawler->text();
                $content .= $text;

                if (! empty($text)) {
                    Log::warning("No extractor for <{$elementName}>");
                }
            }
        });

        return $content;
    }

    public static function getFilter(): string
    {
        return '*';
    }

    public static function canParse(Crawler $page): array
    {
        $count = $page->filterXPath('//*[starts-with(local-name(), "g-")]')->count();

        return [
            $count > 0,
            $count > 0 ? PHP_INT_MAX : 0,
        ];
    }

    private function extractWithTrait(Crawler $crawler, string $elementName): string
    {
        $method = TraitMethodRegistry::getMethod($elementName);

        if ($method !== null && method_exists($this, $method)) {
            return $this->{$method}($crawler);
        }

        return '';
    }
}
