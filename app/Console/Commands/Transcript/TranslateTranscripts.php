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
    protected $signature = 'translate:transcripts';

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
        dispatch(new \App\Jobs\Relay\Transcript\Translate\TranslateTranscripts());
    }
}
