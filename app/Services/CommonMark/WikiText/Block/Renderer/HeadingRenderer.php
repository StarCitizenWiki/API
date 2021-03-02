<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Block\Renderer;

use InvalidArgumentException;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;

class HeadingRenderer implements BlockRendererInterface
{
    private bool $bumpH1;

    /**
     * @param bool $bumpH1 Bumps H1 to H2 if flag is set
     */
    public function __construct(bool $bumpH1 = true)
    {
        $this->bumpH1 = $bumpH1;
    }

    /**
     * @param AbstractBlock $block
     * @param ElementRendererInterface $htmlRenderer
     * @param bool $inTightList
     *
     * @return string
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, bool $inTightList = false): string
    {
        if (!($block instanceof Heading)) {
            throw new InvalidArgumentException('Incompatible block type: ' . \get_class($block));
        }

        $level = $block->getLevel();
        if ($this->bumpH1 === true && $level === 1) {
            $level = 2;
        }

        $pad = str_pad('', $level, '=');

        return sprintf(
            '%s %s %s',
            $pad,
            $block->getStringContent(),
            $pad,
        );
    }
}
