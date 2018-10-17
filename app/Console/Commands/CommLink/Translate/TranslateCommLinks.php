<?php declare(strict_types = 1);

namespace App\Console\Commands\CommLink\Translate;

use Illuminate\Console\Command;

class TranslateCommLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:comm-links';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all Comm-Links using DeepL';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Dispatching Comm-Link Translation');

        dispatch(new \App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks());
    }
}
