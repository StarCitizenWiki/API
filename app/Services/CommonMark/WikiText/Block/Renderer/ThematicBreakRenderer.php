<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Block\Renderer;

use InvalidArgumentException;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\ThematicBreak;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;

class ThematicBreakRenderer implements BlockRendererInterface
{
    /**
     * @inheritDoc
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, bool $inTightList = false)
    {
        if (!($block instanceof ThematicBreak)) {
            throw new InvalidArgumentException('Incompatible block type: ' . \get_class($block));
        }

        return "----";
    }
}
