<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks as TranslateCommLinksJob;
use Illuminate\Console\Command;

class TranslateCommLinks extends Command
{
    protected $signature = 'comm-link:translate';

    protected $description = 'Translate all untranslated Comm-Links using DeepL';

    public function handle(): int
    {
        $this->info('Dispatching Comm-Link Translation');

        TranslateCommLinksJob::dispatch();

        return Command::SUCCESS;
    }
}
