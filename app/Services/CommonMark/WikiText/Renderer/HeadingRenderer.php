<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

use function get_class;

class HeadingRenderer implements NodeRendererInterface
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
     * @param Node $node
     * @param ChildNodeRendererInterface $childRenderer
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!($node instanceof Heading)) {
            throw new InvalidArgumentException('Incompatible block type: ' . get_class($node));
        }

        $level = $node->getLevel();
        if ($this->bumpH1 === true && $level === 1) {
            $level = 2;
        }

        $pad = str_pad('', $level, '=');

        return sprintf(
            '%s %s %s',
            $pad,
            $childRenderer->renderNodes($node->children()),
            $pad,
        );
    }
}
