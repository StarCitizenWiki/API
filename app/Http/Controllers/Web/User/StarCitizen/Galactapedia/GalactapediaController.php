<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\User\StarCitizen\Galactapedia;

use App\Http\Controllers\Controller;
use App\Models\Api\StarCitizen\Galactapedia\Article;
use App\Models\System\ModelChangelog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;

class GalactapediaController extends Controller
{
    /**
     * @return View
     */
    public function index(): View
    {
        return view(
            'user.starcitizen.galactapedia.index',
            [
                'articles' => Article::query()->paginate(50),
            ]
        );
    }

    public function show(string $article): View
    {
        $article = Article::query()->where('cig_id', $article)->firstOrFail();

        /** @var Collection $changelogs */
        $changelogs = $article->changelogs;

        //$changelogs = $changelogs->merge($article->translationChangelogs);

        $article->textChanges = 0;

        $changelogs->each(
            static function (ModelChangelog $changelog) use ($article) {
                if (!isset($changelog->changelog['changes']['translation'])) {
                    return;
                }

                $article->textChanges++;

                $builder = new StrictUnifiedDiffOutputBuilder(
                    [
                        'collapseRanges' => true,
                        'commonLineThreshold' => 1,
                        'contextLines' => 0,
                        'fromFile' => $article->created_at->toString(),
                        'fromFileDate' => '',
                        'toFile' => $changelog->created_at->toString(),
                        'toFileDate' => '',
                    ]
                );

                $differ = new Differ($builder);

                $changelog->diff = ($differ->diff(
                    $changelog->changelog['changes']['translation']['old'],
                    $changelog->changelog['changes']['translation']['new'],
                ));
            }
        );

        $changelogs = $changelogs->sortByDesc('created_at');

        return view(
            'user.starcitizen.galactapedia.show',
            [
                'article' => $article,
                'changelogs' => $changelogs,
                'prev' => $article->getPrevAttribute(),
                'next' => $article->getNextAttribute(),
            ]
        );
    }
}
