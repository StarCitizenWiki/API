<?php

declare(strict_types=1);

namespace App\Services\CommonMark\WikiText\Inline\Renderer\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use function get_class;

class LinkRenderer implements InlineRendererInterface
{
    /**
     * @param AbstractInline $inline
     * @param ElementRendererInterface $htmlRenderer
     *
     * @return string
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer): string
    {
        if (!($inline instanceof Link)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        $urlText = $htmlRenderer->renderInlines($inline->children());

        $path = parse_url($inline->getUrl())['path'];
        $id = last(explode('/', $path));
        $id = explode('-', $id)[0];

        try {
            $article = Article::query()->where('cig_id', $id)->firstOrFail();

            if ($article->title !== $urlText) {
                return sprintf(
                    '[[%s|%s]]',
                    $article->cleanTitle,
                    $urlText,
                );
            }

            $urlText = $article->cleanTitle;
        } catch (ModelNotFoundException $e) {
            //
        }

        return sprintf(
            '[[%s]]',
            $urlText,
        );
    }
}
