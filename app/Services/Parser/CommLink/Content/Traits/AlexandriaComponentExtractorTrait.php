<?php

declare(strict_types=1);

namespace App\Services\Parser\CommLink\Content\Traits;

use Symfony\Component\DomCrawler\Crawler;

trait AlexandriaComponentExtractorTrait
{
    use JsonDecoderTrait;

    public function getAlexandriaComponents(Crawler $page): string
    {
        $content = '';

        $page->filterXPath('//g-platform-client-component')->each(function (Crawler $crawler) use (&$content): void {
            /** @var array{componentId?: string, componentProps?: array<string, mixed>} $propertiesJson */
            $propertiesJson = $this->decodeJsonAttribute($crawler, ':properties');
            if ($propertiesJson === null) {
                return;
            }

            $componentId = $propertiesJson['componentId'] ?? null;
            if (! is_string($componentId) || $componentId === '') {
                return;
            }

            $componentProps = $propertiesJson['componentProps'] ?? [];
            if (! is_array($componentProps)) {
                $componentProps = [];
            }

            $component = $this->extractAlexandriaComponent($componentId, $componentProps);
            if ($component === '') {
                return;
            }

            $content .= $component;
        });

        return $content;
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractAlexandriaComponent(string $componentId, array $componentProps): string
    {
        return match ($componentId) {
            'Text' => $this->extractTextComponent($componentProps),
            'Image' => $this->extractImageComponent($componentProps),
            'Video' => $this->extractVideoComponent($componentProps),
            'Quote' => $this->extractQuoteComponent($componentProps),
            'Gallery' => $this->extractGalleryComponent($componentProps),
            'Button' => $this->extractButtonComponent($componentProps),
            'CallToAction' => $this->extractCallToActionComponent($componentProps),
            'MiniGrid' => $this->extractMiniGridComponent($componentProps),
            'Separator' => $this->extractSeparatorComponent($componentProps),
            'Background' => $this->extractBackgroundComponent($componentProps),
            'OrionCardsList' => $this->extractOrionCardsListComponent($componentProps),
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractTextComponent(array $componentProps): string
    {
        $out = [];

        if (! empty($componentProps['text'] ?? null)) {
            $out[] = sprintf('<p>%s</p>', $componentProps['text']);
        }

        if (! empty($componentProps['title'] ?? null)) {
            $out[] = sprintf('<h2>%s</h2>', $componentProps['title']);
        }

        if (! empty($componentProps['description'] ?? null)) {
            $out[] = sprintf('<p>%s</p>', $componentProps['description']);
        }

        return collect($out)->implode("\n");
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractImageComponent(array $componentProps): string
    {
        $altText = $componentProps['altText'] ?? '';
        if (! is_string($altText)) {
            $altText = '';
        }

        $figcaptionParts = [];

        if (! empty($componentProps['caption'] ?? null)) {
            $figcaptionParts[] = (string) $componentProps['caption'];
        }

        if (! empty($componentProps['title'] ?? null)) {
            $figcaptionParts[] = (string) $componentProps['title'];
        }

        $figcaption = collect($figcaptionParts)->implode(' - ');

        if ($altText === '' && $figcaption === '') {
            return '';
        }

        $figcaptionTag = $figcaption !== ''
            ? sprintf('<figcaption>%s</figcaption>', $figcaption)
            : '';

        return sprintf('<figure><img alt="%s" />%s</figure>', $altText, $figcaptionTag);
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractVideoComponent(array $componentProps): string
    {
        $out = [];

        if (! empty($componentProps['title'] ?? null)) {
            $out[] = sprintf('<h2>%s</h2>', $componentProps['title']);
        }

        if (! empty($componentProps['description'] ?? null)) {
            $out[] = sprintf('<p>%s</p>', $componentProps['description']);
        }

        return collect($out)->implode("\n");
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractQuoteComponent(array $componentProps): string
    {
        if (empty($componentProps['text'] ?? null)) {
            return '';
        }

        $citeParts = [];

        if (! empty($componentProps['author'] ?? null)) {
            $citeParts[] = (string) $componentProps['author'];
        }

        if (! empty($componentProps['source'] ?? null)) {
            $citeParts[] = (string) $componentProps['source'];
        }

        $cite = collect($citeParts)->implode(', ');
        $citeTag = $cite !== ''
            ? sprintf('<cite>%s</cite>', $cite)
            : '';

        return sprintf('<blockquote><p>%s</p>%s</blockquote>', $componentProps['text'], $citeTag);
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractGalleryComponent(array $componentProps): string
    {
        $out = [];

        if (! empty($componentProps['title'] ?? null)) {
            $out[] = sprintf('<h2>%s</h2>', $componentProps['title']);
        }

        $items = $componentProps['items'] ?? null;
        if (is_array($items) && $items !== []) {
            $lis = collect($items)
                ->filter(fn (mixed $item): bool => is_string($item) && $item !== '')
                ->map(fn (string $item): string => sprintf('<li>%s</li>', $item))
                ->implode('');

            if ($lis !== '') {
                $out[] = sprintf('<ul>%s</ul>', $lis);
            }
        }

        return collect($out)->implode("\n");
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractButtonComponent(array $componentProps): string
    {
        if (empty($componentProps['label'] ?? null)) {
            return '';
        }

        return sprintf('<button>%s</button>', $componentProps['label']);
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractCallToActionComponent(array $componentProps): string
    {
        $out = [];

        if (! empty($componentProps['title'] ?? null)) {
            $out[] = sprintf('<h2>%s</h2>', $componentProps['title']);
        }

        if (! empty($componentProps['description'] ?? null)) {
            $out[] = sprintf('<p>%s</p>', $componentProps['description']);
        }

        if (! empty($componentProps['buttonLabel'] ?? null)) {
            $out[] = sprintf('<button>%s</button>', $componentProps['buttonLabel']);
        }

        return collect($out)->implode("\n");
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractMiniGridComponent(array $componentProps): string
    {
        $elements = $componentProps['gridOptions']['uiData']['elements'] ?? null;
        if (! is_array($elements)) {
            return '';
        }

        $out = [];

        foreach ($elements as $element) {
            if (! is_array($element)) {
                continue;
            }

            $key = $element['key'] ?? null;

            if ($key === 'mg.text') {
                $text = $element['data']['text'] ?? null;
                if (is_string($text) && $text !== '') {
                    $decodedText = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                    $out[] = $decodedText;
                }
            } elseif ($key === 'mg.media') {
                $mediaData = $element['data'] ?? [];
                if (is_array($mediaData)) {
                    $media = $this->extractMiniGridMedia($mediaData);
                    if ($media !== '') {
                        $out[] = $media;
                    }
                }
            }
        }

        return collect($out)->implode("\n");
    }

    /**
     * @param  array<string, mixed>  $mediaData
     */
    private function extractMiniGridMedia(array $mediaData): string
    {
        // Handle image from heapImage structure
        $imageSource = $mediaData['image']['heapImage']['source'] ?? null;
        if (is_string($imageSource) && $imageSource !== '') {
            $altText = $mediaData['image']['heapImage']['imageConfiguration']['imageDescription']['cropperInformations']['altText'] ?? '';
            if (! is_string($altText)) {
                $altText = '';
            }

            return sprintf('<img src="%s" alt="%s" />', $imageSource, $altText);
        }

        // Handle video from heapVideo structure
        $videoSource = $mediaData['video']['heapVideo']['source'] ?? null;
        if (is_string($videoSource) && $videoSource !== '') {
            return sprintf('<video src="%s"></video>', $videoSource);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractSeparatorComponent(array $componentProps): string
    {
        return ''; // Separator is visual - no content to extract
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractBackgroundComponent(array $componentProps): string
    {
        $selector = $componentProps['selector'] ?? '';
        $color = $componentProps['backgroundColor'] ?? '';

        if ($selector === '' && $color === '') {
            return '';
        }

        return sprintf('<!-- Background: selector=%s color=%s -->', $selector, $color);
    }

    /**
     * @param  array<string, mixed>  $componentProps
     */
    private function extractOrionCardsListComponent(array $componentProps): string
    {
        $cards = $componentProps['cards'] ?? [];
        if (! is_array($cards) || $cards === []) {
            return '';
        }

        $out = [];

        foreach ($cards as $card) {
            if (! is_array($card)) {
                continue;
            }

            $title = $card['title'] ?? '';
            $description = $card['description'] ?? '';

            if ($title !== '' && is_string($title)) {
                $out[] = sprintf('<h3>%s</h3>', $title);
            }

            if ($description !== '' && is_string($description)) {
                $out[] = sprintf('<p>%s</p>', $description);
            }
        }

        return collect($out)->implode("\n");
    }
}
