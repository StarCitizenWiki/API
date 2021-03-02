<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Inline\Renderer;

use InvalidArgumentException;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use function get_class;

class LinkRenderer implements InlineRendererInterface
{
    /**
     * @param AbstractInline $inline
     * @param ElementRendererInterface $htmlRenderer
     *
     * @return string
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer): string
    {
        if (!($inline instanceof Link)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        return sprintf(
            '[%s %s]',
            $inline->getUrl(),
            $htmlRenderer->renderInlines($inline->children())
        );
    }
}
