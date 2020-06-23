<?php

namespace App\Console\Commands\CommLink;

use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

class SyncImageIds extends Command
{
    const FIRST_COMM_LINK_ID = 12663;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:sync-images {offset=0 : Comm-Link start ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Parses all Comm-Links and syncs found images.';

    /**
     * @var Dispatcher
     */
    private $dispatcher;

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
        $this->info('Dispatching Comm-Link Image Sync');
        $offset = (int) $this->argument('offset');
        if ($offset >= 0) {
            if ($offset < self::FIRST_COMM_LINK_ID) {
                $offset = self::FIRST_COMM_LINK_ID + $offset;
            }

            $this->info("Starting at Comm-Link ID {$offset}");
        }

        $this->dispatcher->dispatch(new \App\Jobs\CommLink\SyncImageIds($offset));

        return 0;
    }
}
