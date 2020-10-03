<?php

declare(strict_types=1);

namespace App\Console\Commands\CommLink\Image;

use App\Console\Commands\CommLink\AbstractCommLinkCommand as CommLinkCommand;
use Illuminate\Bus\Dispatcher;

class SyncImageIds extends CommLinkCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:sync-image-ids {offset=0 : Comm-Link start ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-Parse all local Comm-Links and sync the images';

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
        $this->info('Dispatching Comm-Link Image Sync');

        $offset = $this->parseOffset();

        $this->info("Starting at Comm-Link ID {$offset}");

        $this->dispatcher->dispatch(new \App\Jobs\Rsi\CommLink\SyncImageIds($offset));

        return 0;
    }
}
