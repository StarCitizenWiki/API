<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

use function preg_replace;

class ImageRenderer implements NodeRendererInterface
{
    /**
     * @param Node $node
     * @param ChildNodeRendererInterface $childRenderer
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!($node instanceof Image)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . \get_class($node));
        }

        $alt = $childRenderer->renderNodes($node->children());
        $alt = preg_replace('/\<[^>]*alt="([^"]*)"[^>]*\>/', '$1', $alt);
        $alt = preg_replace('/\<[^>]*\>/', '', $alt);

        return sprintf(
            '[[File:%s|alt=%s|class=%s]]',
            last(explode('/', (parse_url($node->getUrl())['path'] ?? ''))) ?? '',
            $alt,
            ''
        );
    }
}
