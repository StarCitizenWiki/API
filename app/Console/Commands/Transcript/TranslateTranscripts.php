<?php

declare(strict_types=1);

namespace App\Console\Commands\Transcript;

use Illuminate\Console\Command;

class TranslateTranscripts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:transcripts {limit=0 : Max jobs to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate all Transcripts using DeepL';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Dispatching Transcript Translation');
        if (null === $this->argument('startPage')) {
            dispatch(new \App\Jobs\Transcript\Translate\TranslateTranscripts());
        } else {
            dispatch(new \App\Jobs\Transcript\Translate\TranslateTranscripts((int) $this->argument('limit')));
        }
    }
}
