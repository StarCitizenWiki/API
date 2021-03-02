<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Inline\Renderer;

use InvalidArgumentException;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

use function preg_replace;

class ImageRenderer implements InlineRendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof Image)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . \get_class($inline));
        }

        $attrs = $inline->getData('attributes', []);

        $alt = $htmlRenderer->renderInlines($inline->children());
        $alt = preg_replace('/\<[^>]*alt="([^"]*)"[^>]*\>/', '$1', $alt);
        $alt = preg_replace('/\<[^>]*\>/', '', $alt);

        return sprintf(
            '[[File:%s|alt=%s|class=%s]]',
            last(explode('/', (parse_url($inline->getUrl())['path'] ?? ''))) ?? '',
            $alt,
            $attrs['class'] ?? ''
        );
    }
}
