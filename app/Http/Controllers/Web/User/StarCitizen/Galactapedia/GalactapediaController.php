<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Galactapedia;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Galactapedia\Article;
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
                'articles' => Article::query()->paginate(250),
            ]
        );
    }

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
                'changelogs' => $this->diffTranslations($changelogs, $article),
                'prev' => $article->getPrevAttribute(),
                'next' => $article->getNextAttribute(),
            ]
        );
    }
}
