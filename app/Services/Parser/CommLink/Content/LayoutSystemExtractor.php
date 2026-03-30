<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content;

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
use DOMElement;
use DOMNode;
use DOMXPath;
use Symfony\Component\DomCrawler\Crawler;

final class LayoutSystemExtractor implements ContentExtractorInterface
{
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

    private static array $extractionOrder = [
        'g-introduction',
        'g-tumbril-features',
        'g-explore',
        'g-grid',
        'g-banner-advanced',
        'g-skus',
        'g-narrative-group',
        'g-illustration',
        'g-author',
        'g-faq',
        'g-header',
        'g-article',
    ];

    public function __construct(public Crawler $page) {}

    public function getContent(): string
    {
        $content = '';

        foreach (self::$extractionOrder as $element) {
            $method = TraitMethodRegistry::getMethod($element);

            if ($method !== null && method_exists($this, $method)) {
                $content .= $this->{$method}($this->page);
            }
        }

        $this->page->filter(self::getFilter())->each(function (Crawler $crawler) use (&$content): void {
            $content .= $this->residualLayoutMarkup($crawler);
        });

        return $content;
    }

    private function residualLayoutMarkup(Crawler $crawler): string
    {
        $layoutNode = $crawler->getNode(0);
        if (! $layoutNode instanceof DOMElement) {
            return '';
        }

        $layoutClone = $layoutNode->cloneNode(true);
        if (! $layoutClone instanceof DOMElement) {
            return '';
        }

        $xpath = new DOMXPath($layoutClone->ownerDocument);
        foreach ($xpath->query($this->extractedElementsXPath(), $layoutClone) as $node) {
            if ($node instanceof DOMNode && $node->parentNode !== null) {
                $node->parentNode->removeChild($node);
            }
        }

        $markup = '';
        foreach ($layoutClone->childNodes as $childNode) {
            $markup .= $layoutClone->ownerDocument->saveHTML($childNode);
        }

        return ltrim($markup);
    }

    private function extractedElementsXPath(): string
    {
        return './/'.implode(' | .//', self::$extractionOrder);
    }

    private function getVueArticleContent(Crawler $page): string
    {
        return (new VueArticleExtractor($page))->getContent(false);
    }

    public static function getFilter(): string
    {
        return '#layout-system';
    }

    public static function canParse(Crawler $page): array
    {
        $count = $page->filter(self::getFilter())->count();

        return [
            $count > 0,
            $count + 10,
        ];
    }
}
