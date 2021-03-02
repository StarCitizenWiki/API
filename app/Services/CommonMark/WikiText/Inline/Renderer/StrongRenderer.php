<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Inline\Renderer;

use InvalidArgumentException;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Strong;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use function get_class;

class StrongRenderer implements InlineRendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof Strong)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        return sprintf(
            "'''%s'''",
            $htmlRenderer->renderInlines($inline->children())
        );
    }
}
