<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Download\DownloadCommLink;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use Illuminate\Console\Command;

class DownloadCommLinks extends Command
{
    private const FIRST_COMM_LINK_ID = 12663;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:download
        {ids* : Comm-Link IDs}
        {--import : Import Comm-Links after download}
        {--overwrite : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download Comm-Links for the given IDs.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ids = collect($this->argument('ids'))
            ->filter(static fn ($id) => is_numeric($id))
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn (int $id) => $id >= self::FIRST_COMM_LINK_ID)
            ->values();

        if ($ids->isEmpty()) {
            $this->error('No valid Comm-Link IDs provided.');

            return self::FAILURE;
        }

        $skipExisting = ! $this->option('overwrite');

        $ids->each(function (int $id) use ($skipExisting): void {
            $this->info(sprintf('Dispatching download for Comm-Link %d', $id));
            dispatch(new DownloadCommLink($id, $skipExisting));
        });

        if ($this->option('import')) {
            $this->info('Dispatching import for recently downloaded Comm-Links.');
            dispatch(new ImportCommLinks(30));
        }

        return self::SUCCESS;
    }
}
