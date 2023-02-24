<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

use function get_class;

class ThematicBreakRenderer implements NodeRendererInterface
{
    /**
     * @param Node $node
     * @param ChildNodeRendererInterface $childRenderer
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!($node instanceof ThematicBreak)) {
            throw new InvalidArgumentException('Incompatible block type: ' . get_class($node));
        }

        return "----";
    }
}
