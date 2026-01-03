<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink;

use App\Jobs\Rsi\CommLink\Download\ReDownloadDbCommLinks;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks;
use Illuminate\Console\Command;

class RedownloadCommLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-link:download-new-versions {--skip=true : Skip already downloaded Comm-Links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-download and import Comm-Links from the database.';

    public function handle(): int
    {
        $skip = filter_var($this->option('skip'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $skipExisting = $skip ?? true;

        ReDownloadDbCommLinks::withChain([
            new ImportCommLinks(-1),
        ])->dispatch($skipExisting);

        return self::SUCCESS;
    }
}
