<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use function get_class;

class EmphasisRenderer implements NodeRendererInterface
{
    /**
     * @param Node $node
     * @param ChildNodeRendererInterface $childRenderer
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!($node instanceof Emphasis)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . get_class($node));
        }

        return sprintf("''%s''", $childRenderer->renderNodes($node->children()));
    }
}
