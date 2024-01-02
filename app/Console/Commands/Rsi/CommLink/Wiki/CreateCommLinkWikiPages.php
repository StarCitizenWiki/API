<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink\Wiki;

use Illuminate\Console\Command;

class CreateCommLinkWikiPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:create-wiki-pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Wiki Comm-Link Pages for all translated Comm-Links';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Comm-Link Wiki Page Creation');

        \App\Jobs\Wiki\CommLink\CreateCommLinkWikiPages::dispatch();

        return Command::SUCCESS;
    }
}
