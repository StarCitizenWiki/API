<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Galactapedia\Wiki;

use App\Jobs\Wiki\Galactapedia\CreateGalactapediaWikiPages;
use Illuminate\Console\Command;

class CreateWikiPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'galactapedia:create-wiki-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Wiki Galactapedia Pages for all translated Articles';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Galactapedia Wiki Page Creation');

        dispatch(new CreateGalactapediaWikiPages());

        return 0;
    }
}
