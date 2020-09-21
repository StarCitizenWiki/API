<?php declare(strict_types=1);

namespace App\Console\Commands\CommLink\Translate;

use App\Console\Commands\CommLink\AbstractCommLinkCommand as CommLinkCommand;

class TranslateCommLinks extends CommLinkCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:translate {offset=0 : Comm-Link start ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all Comm-Links using DeepL';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Comm-Link Translation');

        $offset = $this->parseOffset();

        $this->info("Starting at Comm-Link ID {$offset}");

        dispatch(new \App\Jobs\Rsi\CommLink\Translate\TranslateCommLinks($offset));

        return 0;
    }
}
