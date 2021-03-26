<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia\Wiki;

use App\Console\Commands\AbstractQueueCommand;
use App\Jobs\Wiki\Galactapedia\UploadGalactapediaWikiImages;
use App\Models\StarCitizen\Galactapedia\Article;
use Illuminate\Support\Collection;

class UploadImages extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:upload-wiki-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload all galactapdia images to the wiki';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Galactapedia image upload');

        $articles = Article::all();

        $this->createProgressBar($articles->count());

        Article::all()->chunk(100)->each(function (Collection $collection) {
            $collection->each(function (Article $article) {
                UploadGalactapediaWikiImages::dispatch($article);
                $this->advanceBar();
            });
        });

        return 0;
    }
}
