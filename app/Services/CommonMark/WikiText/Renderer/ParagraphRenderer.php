<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

class ParagraphRenderer implements NodeRendererInterface
{
    /**
     * @param Node $node
     * @param ChildNodeRendererInterface $childRenderer
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        return sprintf("%s\n", $childRenderer->renderNodes($node->children()));
    }
}
