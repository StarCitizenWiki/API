<?php

declare(strict_types=1);

namespace App\Services\CommonMark;

use App\Services\CommonMark\WikiText\Renderer\EmphasisRenderer;
use App\Services\CommonMark\WikiText\Renderer\Galactapedia\LinkRenderer;
use App\Services\CommonMark\WikiText\Renderer\HeadingRenderer;
use App\Services\CommonMark\WikiText\Renderer\ParagraphRenderer;
use App\Services\CommonMark\WikiText\Renderer\StrongRenderer;
use App\Services\CommonMark\WikiText\Renderer\ThematicBreakRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;

/**
 * CommonMark is a extensible MarkDown parser
 * This is mainly used for parsing Galactapedia articles to a format that is understood by MediaWiki
 */
final class WikiTextRenderer
{
    /**
     * @var MarkdownParser
     */
    private MarkdownParser $parser;

    /**
     * @var HtmlRenderer
     */
    private HtmlRenderer $renderer;

    /**
     * Adds new renderer definitions
     */
    public function __construct(bool $useLanguageLinks = false)
    {
        $environment = new Environment([
            'html_input' => 'strip',
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addRenderer(Heading::class, new HeadingRenderer());
        $environment->addRenderer(ThematicBreak::class, new ThematicBreakRenderer());
        $environment->addRenderer(Paragraph::class, new ParagraphRenderer());
        $environment->addRenderer(Link::class, new LinkRenderer($useLanguageLinks));
        $environment->addRenderer(Emphasis::class, new EmphasisRenderer());
        $environment->addRenderer(Strong::class, new StrongRenderer());

        $this->parser = new MarkdownParser($environment);
        $this->renderer = new HtmlRenderer($environment);
    }

    public function render(string $markdown): string
    {
        return $this->renderer->renderNodes($this->parser->parse($markdown)->children());
    }
}
