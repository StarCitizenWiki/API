<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink;

use DOMElement;
use DOMXPath;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Generic collector over g-* custom elements
 */
final class JsonWalker
{
    private const SKIP_KEY_FRAGMENTS = [
        'dispositions', 'animations', 'transition', 'backgroundoptions', 'layout', 'styles',
        'position', 'padding', 'dimension', 'arrangement', 'alignment', 'spacing', 'zindex',
        'breakpoint', 'aspectratio', 'duration', 'autoplay', 'loop', 'muted', 'controls',
        'id', 'slug', 'typename', 'key', 'type', 'theme', 'appearance', 'color',
        'backgroundcolor', 'selector', 'columns', 'gap', 'margin', 'offset', 'icon',
        'coordinates', 'componentid', 'componentname', 'animationoptions', 'parallax',
        'label', 'link', 'href', 'url', 'cta', 'alt', 'caption', 'placeholder',
    ];

    private const TEXT_KEYS = [
        'title', 'text', 'body', 'content', 'contents', 'subtitle', 'description', 'summary',
        'headline', 'byline', 'overline', 'paragraph', 'question', 'answer', 'author', 'quote',
    ];

    private const IMAGE_EXTENSION_PATTERN = '/\.(?:jpe?g|png|webp|gif|svg|avif|mp4)$/i';

    private const FILENAME_SUFFIX_PATTERN = '/\.(?:jpe?g|png|webp|gif|svg|avif|mp4|webm|mov|css|mjs|js|json|woff2?|ttf|otf|eot|pdf|zip|gz|mp3|wav|ogg|m4a|vtt|srt|xml|html?|glb|gltf|obj|fbx|usdz|heic|tiff?|bmp|ico)$/i';

    /**
     * @var list<string>
     */
    private array $textBlocks = [];

    /**
     * @var array<string, true>
     */
    private array $seenTextBlocks = [];

    /**
     * @var list<string>
     */
    private array $images = [];

    /**
     * @var list<string>
     */
    private array $urls = [];

    public function collect(Crawler $crawler): WalkerResult
    {
        $this->textBlocks = [];
        $this->seenTextBlocks = [];
        $this->images = [];
        $this->urls = [];

        $crawler->filterXPath('//*[starts-with(local-name(), "g-")]')->each(function (Crawler $element): void {
            $this->collectElement($element);
        });

        return new WalkerResult(
            $this->textBlocks,
            array_values(array_unique($this->images)),
            array_values(array_unique($this->urls)),
        );
    }

    private function collectElement(Crawler $element): void
    {
        $node = $element->getNode(0);

        if (! $node instanceof DOMElement) {
            return;
        }

        $suppressText = $node->nodeName === 'g-navigation-sales';
        $textCountBefore = count($this->textBlocks);

        foreach ($node->attributes as $attribute) {
            $name = $attribute->name;

            if ($name === '' || $name === 'v-cloak') {
                continue;
            }

            if (str_starts_with($name, ':')) {
                $decoded = json_decode($attribute->value, true, 512);

                if (is_array($decoded) || is_string($decoded)) {
                    $this->walkValue($decoded, mb_substr($name, 1), $suppressText);
                }

                continue;
            }

            $this->walkValue($attribute->value, $name, $suppressText);
        }

        // No DOM fallback once the attributes produced blocks of their own.
        if ($suppressText || count($this->textBlocks) > $textCountBefore) {
            return;
        }

        $this->fallbackText($node);
    }

    private function fallbackText(DOMElement $node): void
    {
        // Templates emit inner html
        $clone = $node->cloneNode(true);

        if (! $clone instanceof DOMElement) {
            return;
        }

        self::removeNestedGElements($clone);

        $templates = (new DOMXPath($clone->ownerDocument))->query('.//template', $clone);

        foreach ($templates ?: [] as $template) {
            $html = trim((string) (new Crawler($template))->html());
            $text = trim((string) preg_replace('/\s+/u', ' ', $html));

            if ($text !== '') {
                $this->addTextBlock($html);
            }
        }

        $text = trim((string) preg_replace('/\s+/u', ' ', $clone->textContent));

        if ($text !== '') {
            $this->addTextBlock($text);
        }
    }

    private static function removeNestedGElements(DOMElement $node): void
    {
        $nested = (new DOMXPath($node->ownerDocument))
            ->query('.//*[starts-with(local-name(), "g-")]', $node) ?: [];

        // snapshot so to not live mutate list
        foreach (iterator_to_array($nested) as $child) {
            $child->parentNode?->removeChild($child);
        }
    }

    private function walkValue(mixed $value, string $key, bool $suppressText): void
    {
        if (is_string($value)) {
            $this->classifyLeaf($value, $key, $suppressText || $this->isSkippedKey($key));

            return;
        }

        if (! is_array($value)) {
            return;
        }

        if (mb_strtolower($key) === 'dispositions') {
            $this->walkDispositions($value, $suppressText);

            return;
        }

        if ($this->isPictureArray($value)) {
            foreach ($this->pictureUrls($value) as $url) {
                $this->addImage($url);
            }

            return;
        }

        foreach ($value as $childKey => $childValue) {
            if (is_string($childKey)) {
                $childSkipped = mb_strtolower($childKey) !== 'dispositions' && $this->isSkippedKey($childKey);

                $this->walkValue($childValue, $childKey, $suppressText || $childSkipped);
            } else {
                $this->walkValue($childValue, $key, $suppressText);
            }
        }
    }

    /**
     * Corpus shape is {small, medium, large} holding pure layout junk
     *
     * @param  array<string, mixed>  $value
     */
    private function walkDispositions(array $value, bool $suppressText): void
    {
        $display = $value['display'] ?? null;

        if (! is_array($display)) {
            return;
        }

        foreach (['large', 'medium', 'small'] as $breakpoint) {
            $breakpointValue = $display[$breakpoint] ?? null;

            if (is_string($breakpointValue) || is_array($breakpointValue)) {
                $this->walkValue($breakpointValue, $breakpoint, $suppressText);

                return;
            }
        }
    }

    private function classifyLeaf(string $rawValue, string $key, bool $suppressText): void
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $rawValue));

        if ($value === '' || str_starts_with($value, 'data:')) {
            return;
        }

        // Rich text often embeds media urls, so HTML wins over image matching
        if ($this->isHtmlString($value)) {
            if (! $suppressText) {
                $this->addTextBlock($value);
            }

            return;
        }

        if ($this->isImageUrl($value)) {
            $this->addImage($value);

            return;
        }

        if (preg_match('~^https?://~i', $value) === 1) {
            $this->captureLinkUrl($value, $key);

            return;
        }

        if (! $suppressText && $this->isEditorialText($value, $key)) {
            $this->addTextBlock($value);
        }
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function isPictureArray(array $value): bool
    {
        foreach (['originalFormat', 'webp', 'format'] as $variantKey) {
            $variant = $value[$variantKey] ?? null;

            if (is_array($variant) && $variant !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $value
     * @return list<string>
     */
    private function pictureUrls(array $value): array
    {
        $original = $this->pictureVariantUrl($value, 'originalFormat');
        $webp = $this->pictureVariantUrl($value, 'webp');

        $urls = [];

        foreach ([$original, $webp] as $url) {
            if ($url !== null && ! in_array($url, $urls, true)) {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function pictureVariantUrl(array $value, string $variantKey): ?string
    {
        $variant = $value[$variantKey] ?? null;

        if (is_string($variant)) {
            return $variant !== '' ? $variant : null;
        }

        if (! is_array($variant)) {
            return null;
        }

        foreach (['source', 'max', 'desktop'] as $urlKey) {
            $url = $variant[$urlKey] ?? null;

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return null;
    }

    private function captureLinkUrl(string $value, string $key): void
    {
        if (! in_array(mb_strtolower($key), ['link', 'href', 'url', 'calltoactionlink', 'cta'], true)) {
            return;
        }

        if (preg_match('~^https?://~i', $value) === 1) {
            $this->addUrl($value);
        }
    }

    private function isSkippedKey(string $key): bool
    {
        $lower = mb_strtolower($key);
        $segments = $this->keySegments($key);

        foreach (self::SKIP_KEY_FRAGMENTS as $fragment) {
            // Short fragments match whole key segments only, otherwise "id" would swallow data containers like "uiData" or "gridOptions".
            if (mb_strlen($fragment) <= 4) {
                if (in_array($fragment, $segments, true)) {
                    return true;
                }

                continue;
            }

            if (str_contains($lower, $fragment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function keySegments(string $key): array
    {
        $segments = preg_split('/[^a-zA-Z0-9]+|(?<=[a-z0-9])(?=[A-Z])/', $key) ?: [];

        return array_map(
                static fn(string $segment): string => mb_strtolower($segment),
                $segments,
            )
                |> array_filter(...)
                |> array_values(...);
    }

    private function isEditorialText(string $value, string $key): bool
    {
        if (array_intersect($this->keySegments($key), self::TEXT_KEYS) !== []) {
            return true;
        }

        return mb_strlen($value) >= 20 && ! $this->isNoise($value);
    }

    private function isNoise(string $value): bool
    {
        return preg_match('~^(?:\d+(?:\.\d+)?|#[0-9a-f]{3,8})$~i', $value) === 1
            || preg_match(self::FILENAME_SUFFIX_PATTERN, $value) === 1
            || preg_match('~[\d.](?:%|px|rem|em|vw)$~u', $value) === 1;
    }

    private function isHtmlString(string $value): bool
    {
        return preg_match_all('/<(?:p|ul|ol|li|h[1-6]|br|strong|em|blockquote|figure|a\s)/i', $value) >= 2;
    }

    private function isImageUrl(string $value): bool
    {
        if (str_contains($value, 'media.robertsspaceindustries.com')
            || str_contains($value, 'robertsspaceindustries.com/media')
            || str_starts_with($value, '/i/')) {
            return true;
        }

        return preg_match(self::IMAGE_EXTENSION_PATTERN, $value) === 1;
    }

    private function addTextBlock(string $text): void
    {
        if (isset($this->seenTextBlocks[$text])) {
            return;
        }

        $this->seenTextBlocks[$text] = true;
        $this->textBlocks[] = $text;
    }

    private function addImage(string $src): void
    {
        if (! str_starts_with($src, 'data:')) {
            $this->images[] = $src;
        }
    }

    private function addUrl(string $url): void
    {
        if (! str_starts_with($url, 'data:')) {
            $this->urls[] = $url;
        }
    }
}
