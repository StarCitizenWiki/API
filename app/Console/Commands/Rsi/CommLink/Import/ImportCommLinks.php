<?php

declare(strict_types=1);

namespace App\Console\Commands\Rsi\CommLink\Import;

use App\Console\Commands\Rsi\CommLink\AbstractCommLinkCommand as CommLinkCommand;
use App\Jobs\Rsi\CommLink\Import\ImportCommLinks as ImportCommLinkJobs;
use Illuminate\Bus\Dispatcher;

/**
 * Import all Downloaded Comm-Links.
 */
class ImportCommLinks extends CommLinkCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:import-all {modifiedTime=-1 : Folders to include that were modified in the last minute}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all Comm-Links that were created in the last x minutes';

    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Dispatching Comm-Link Import');

        $offset = (int)$this->argument('modifiedTime');

        if ($offset > 0) {
            $this->info("Including Comm-Links that were created in the last '{$offset}' minutes");
        } elseif ($offset === -1) {
            $this->info('Including all Comm-Links');
        }

        $this->dispatcher->dispatch(new ImportCommLinkJobs($offset));

        return 0;
    }
}
