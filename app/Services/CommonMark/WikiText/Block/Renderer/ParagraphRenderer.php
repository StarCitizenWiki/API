<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Block\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;

class ParagraphRenderer implements BlockRendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, bool $inTightList = false)
    {
        return sprintf("%s\n", $htmlRenderer->renderInlines($block->children()));
    }
}
