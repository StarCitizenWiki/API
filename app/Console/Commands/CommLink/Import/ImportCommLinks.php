<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink\Import;

use App\Console\Commands\CommLink\AbstractCommLinkCommand as CommLinkCommand;
use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
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
    protected $signature = 'comm-links:import-all {offset=0 : Comm-Link start ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all Comm-Links starting at the first, or provided Comm-Link ID';

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

        $offset = $this->parseOffset();

        $this->info("Starting at Comm-Link ID {$offset}");

        $this->dispatcher->dispatch(new ParseCommLinkDownload($offset));

        return 0;
    }
}
