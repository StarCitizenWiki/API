<?php

declare(strict_types=1);

namespace App\Jobs\Wiki\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Traits\GetWikiCsrfTokenTrait as GetWikiCsrfToken;
use ErrorException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Create Galactapedia Wiki Pages.
 */
class CreateGalactapediaWikiPages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use GetWikiCsrfToken;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        app('Log')::info('Starting creation of Galactapedia Wiki Pages');

        try {
            $this->getCsrfToken('services.wiki_translations');
        } catch (ErrorException $e) {
            app('Log')::info(
                sprintf(
                    '%s: %s',
                    'Token retrieval failed',
                    $e->getMessage()
                )
            );

            $this->release(300);

            return;
        }

        Article::query()
            ->with(['properties', 'categories', 'translations', 'related'])
            ->whereHas(
                'translations',
                static function (Builder $query) {
                    $query->where('locale_code', config('services.wiki_translations.locale'))
                        ->whereRaw(
                            "translation <> ''"
                        );
                }
            )
            ->chunk(
                100,
                function (Collection $articles) {
                    $articles->each(function (Article $article) {
                        dispatch(new CreateGalactapediaWikiPage($article, self::$csrfToken));
                    });
                }
            );
    }
}
