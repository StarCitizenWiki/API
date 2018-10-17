<?php declare(strict_types = 1);

namespace App\Console\Commands\CommLink\Wiki;

use Illuminate\Console\Command;

class CreateCommLinkWikiPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wiki:create-comm-link-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Wiki Comm-Link Pages for all translated Comm-Links';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Dispatching Comm-Link Wiki Page Creation');

        dispatch(new \App\Jobs\Wiki\CommLink\CreateCommLinkWikiPages());
    }
}
