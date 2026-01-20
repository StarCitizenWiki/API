<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Console\Command;

class BackfillArticleCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:backfill-counts
        {--chunk=500 : Number of articles to process per chunk}
        {--dry-run : Run command without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill galactapedia article counts from pivot tables.';

    public function handle(): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $query = Article::query()->select('id');

        $updated = 0;

        $query->orderByDesc('id')->chunkById($chunkSize, function ($articles) use (&$updated, $dryRun): void {
            foreach ($articles as $article) {
                $categoriesCount = $article->categories()->count();
                $tagsCount = $article->tags()->count();
                $templatesCount = $article->templates()->count();
                $relatedArticlesCount = $article->related()->count();

                if ($dryRun) {
                    $this->line(sprintf('Article ID %d: categories=%d, tags=%d, templates=%d, related=%d', $article->id, $categoriesCount, $tagsCount, $templatesCount, $relatedArticlesCount));
                } else {
                    $article->update([
                        'categories_count' => $categoriesCount,
                        'tags_count' => $tagsCount,
                        'templates_count' => $templatesCount,
                        'related_articles_count' => $relatedArticlesCount,
                    ]);
                }

                $updated++;
            }

            if (! $dryRun) {
                $this->info(sprintf('Processed %d articles...', $updated));
            }
        });

        if ($dryRun) {
            $this->info(sprintf('Dry run completed for %d articles.', $updated));
        } else {
            $this->info(sprintf('Successfully updated %d articles.', $updated));
        }

        return self::SUCCESS;
    }
}
