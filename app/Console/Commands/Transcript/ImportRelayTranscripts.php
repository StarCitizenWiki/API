<?php

declare(strict_types=1);

namespace App\Console\Commands\Transcript;

use App\Jobs\Relay\Transcript\ImportMissingTranscripts;
use Illuminate\Console\Command;

class ImportRelayTranscripts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcripts:import-relay {startPage=0 : Feed Start Page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import missing relay.sc Transcripts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (null === $this->argument('startPage')) {
            dispatch(new ImportMissingTranscripts());
        } else {
            dispatch(new ImportMissingTranscripts((int)$this->argument('startPage')));
        }

        return 0;
    }
}
