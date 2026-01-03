<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Download\DownloadCommLink;
use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks as ImportCommLinksJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportCommLinks extends Command
{
    private const FIRST_COMM_LINK_ID = 12663;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:import
        {id? : Comm-Link ID}
        {--all : Import all downloaded Comm-Links}
        {--force : Force import even if content matches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Comm-Link HTML from storage.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('all')) {
            dispatch(new ImportCommLinksJob(-1));

            return self::SUCCESS;
        }

        $id = $this->argument('id');

        if ($id === null || ! is_numeric($id)) {
            $this->error('A Comm-Link ID is required unless --all is specified.');

            return self::FAILURE;
        }

        $commLinkId = (int) $id;

        if ($commLinkId < self::FIRST_COMM_LINK_ID) {
            $this->error('Comm-Link ID is below the first known ID.');

            return self::FAILURE;
        }

        $files = Storage::disk('comm_links')->files((string) $commLinkId);

        if ($files === []) {
            $this->warn('Comm-Link file not found locally, dispatching download.');
            dispatch(new DownloadCommLink($commLinkId, true));
            dispatch(new ImportCommLinksJob(30));

            return self::SUCCESS;
        }

        sort($files);
        $file = end($files);

        if ($file === false) {
            $this->error('Unable to determine Comm-Link file.');

            return self::FAILURE;
        }

        $basename = Str::afterLast($file, '/');
        dispatch(new ImportCommLink($commLinkId, $basename, (bool) $this->option('force')));

        return self::SUCCESS;
    }
}
