<?php

declare(strict_types=1);

namespace App\Console\Commands\Transcript;

use App\Console\Commands\AbstractQueueCommand;

final class ImportMetadata extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcripts:import-metadata
    {--chunkAll : Loads 2000 Transcripts per chunk and sorts by upload date. Only really useful for the first import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import transcript metadata';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        \App\Jobs\Transcript\ImportMetadata::dispatch($this->option('chunkAll'));

        return 0;
    }
}
