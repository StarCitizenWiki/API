<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink\Import;

use App\Jobs\Rsi\CommLink\Parser\ParseCommLinkDownload;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Import all Downloaded Comm-Links.
 */
class ImportCommLinks extends Command
{
    const FIRST_COMM_LINK_ID = 12663;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:comm-links {offset=0 : Comm-Link start ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Comm-Links either all or starts at provided Comm-Link ID';

    /**
     * @var \Illuminate\Bus\Dispatcher
     */
    private $dispatcher;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Bus\Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct();

        $this->dispatcher = $dispatcher;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Dispatching Comm-Link Import');
        $offset = (int) $this->argument('offset');
        if ($offset > 0) {
            if ($offset < self::FIRST_COMM_LINK_ID) {
                $offset = self::FIRST_COMM_LINK_ID + $offset;
            }

            $this->info("Starting at Comm-Link ID {$offset}");
        }

        $this->dispatcher->dispatch(new ParseCommLinkDownload($offset));
    }
}
