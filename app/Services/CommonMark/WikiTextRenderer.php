<?php

declare(strict_types=1);

namespace App\Services\CommonMark;

use App\Services\CommonMark\WikiText\Block\Renderer\HeadingRenderer;
use App\Services\CommonMark\WikiText\Block\Renderer\ParagraphRenderer;
use App\Services\CommonMark\WikiText\Block\Renderer\ThematicBreakRenderer;
use App\Services\CommonMark\WikiText\Inline\Renderer\EmphasisRenderer;
use App\Services\CommonMark\WikiText\Inline\Renderer\Galactapedia\LinkRenderer;
use App\Services\CommonMark\WikiText\Inline\Renderer\StrongRenderer;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Block\Element\ThematicBreak;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\EnvironmentInterface;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\Inline\Element\Emphasis;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Strong;

final class WikiTextRenderer
{
    /**
     * @var DocParser
     */
    private DocParser $parser;

    /**
     * @var HtmlRenderer
     */
    private HtmlRenderer $renderer;

    /**
     * @param EnvironmentInterface $environment
     */
    public function __construct()
    {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->setConfig([
            'html_input' => 'strip',
        ]);
        $environment->addBlockRenderer(Heading::class, new HeadingRenderer());
        $environment->addBlockRenderer(ThematicBreak::class, new ThematicBreakRenderer());
        $environment->addBlockRenderer(Paragraph::class, new ParagraphRenderer());
        $environment->addInlineRenderer(Link::class, new LinkRenderer());
        $environment->addInlineRenderer(Emphasis::class, new EmphasisRenderer());
        $environment->addInlineRenderer(Strong::class, new StrongRenderer());

        $this->parser = new DocParser($environment);
        $this->renderer = new HtmlRenderer($environment);
    }

    public function render(string $markdown): string
    {
        return $this->renderer->renderBlock($this->parser->parse($markdown));
    }
}
