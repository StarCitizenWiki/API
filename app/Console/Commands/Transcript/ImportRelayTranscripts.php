<?php

namespace App\Console\Commands\Transcript;

use App\Jobs\Relay\Transcript\Import\ImportMissingTranscripts;
use Illuminate\Console\Command;

class ImportRelayTranscripts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:relay-transcripts {startPage=0 : Feed Start Page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import missing relay.sc Transcripts';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (null === $this->argument('startPage')) {
            dispatch(new ImportMissingTranscripts());
        } else {
            dispatch(new ImportMissingTranscripts((int) $this->argument('startPage')));
        }
    }
}
