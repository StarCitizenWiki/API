<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Galactapedia;

use App\Http\Controllers\Controller;
use App\Jobs\Wiki\Galactapedia\CreateGalactapediaWikiPage;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Traits\DiffTranslationChangelogTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class GalactapediaController extends Controller
{
    use DiffTranslationChangelogTrait;

    /**
     * @return View
     */
    public function index(): View
    {
        return view(
            'user.starcitizen.galactapedia.index',
            [
                'articles' => Article::query()->orderByDesc('id')->paginate(250),
            ]
        );
    }

    /**
     * Shows a singular article
     *
     * @param string $article
     *
     * @return View
     */
    public function show(string $article): View
    {
        $article = Article::query()->where('cig_id', $article)->firstOrFail();

        /** @var Collection $changelogs */
        $changelogs = $article->changelogs;

        $changelogs = $changelogs->merge($article->translationChangelogs);

        return view(
            'user.starcitizen.galactapedia.show',
            [
                'article' => $article,
                'wikitext' => (new CreateGalactapediaWikiPage($article, ''))
                    ->getFormattedText(
                        Article::fixMarkdownLinks($article->german()->translation ?? $article->english()->translation),
                        null
                    ),
                'changelogs' => $this->diffTranslations($changelogs, $article),
                'prev' => $article->getPrevAttribute(),
                'next' => $article->getNextAttribute(),
            ]
        );
    }
}
