<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Renderer\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

use function get_class;

class LinkRenderer implements NodeRendererInterface
{
    private bool $useLanguageLinks;

    /**
     * @param bool $useLanguageLinks Whether to prefix links with Special:MyLanguage/
     */
    public function __construct(bool $useLanguageLinks = false)
    {
        $this->useLanguageLinks = $useLanguageLinks;
    }

    /**
     * @param Node $node
     * @param ChildNodeRendererInterface $childRenderer
     * @return string
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!($node instanceof Link)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . get_class($node));
        }

        $urlText = $childRenderer->renderNodes($node->children());

        $path = parse_url($node->getUrl())['path'];
        $id = last(explode('/', $path));
        $id = explode('-', $id)[0];

        try {
            $article = Article::query()->where('cig_id', $id)->firstOrFail();

            if ($article->title !== $urlText) {
                return sprintf(
                    '[[%s%s|%s]]',
                    $this->useLanguageLinks ? 'Special:MyLanguage/' : '',
                    ...$this->replaceKnownTranslations($article->cleanTitle, $urlText)
                );
            }

            $urlText = $article->cleanTitle;
        } catch (ModelNotFoundException $e) {
            //
        }

        if ($this->useLanguageLinks) {
            return sprintf(
                '[[Special:MyLanguage/%s|%s]]',
                $this->replaceKnownTranslations($urlText, '')[0],
                $this->replaceKnownTranslations($urlText, '')[0]
            );
        }

        return sprintf(
            '[[%s]]',
            $this->replaceKnownTranslations($urlText, '')[0],
        );
    }

    /**
     * WIP
     *
     * @param string $articleTitle
     * @param $urlText
     * @return array
     */
    private function replaceKnownTranslations(string $articleTitle, $urlText): array
    {
        $title = $articleTitle;
        $text = $urlText;

        if ($articleTitle === 'Humans') {
            $title = 'Menschen';
        }

        if ($text === 'Roberts Space Industries (RSI)') {
            $text = 'Roberts Space Industries';
        }

        return [
            $title,
            $text,
        ];
    }
}
