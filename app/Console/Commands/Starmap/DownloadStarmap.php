<?php

declare(strict_types=1);

namespace App\Console\Commands\Starmap;

use App\Jobs\Api\StarCitizen\Starmap\DownloadStarmap as DownloadStarmapJob;
use App\Jobs\Api\StarCitizen\Starmap\Parser\ParseStarmapDownload;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Start Starmap Download Shop
 */
class DownloadStarmap extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starmap:download 
                            {--f|force : Force Download, Overwrite File if exist} 
                            {--i|import : Import System, Celestial Objects and Jumppoint Tunnel after Download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the Starmap Download Job';

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
        $this->info('Dispatching Starmap Download');

        if ($this->option('force')) {
            $this->info('Forcing Download');
        }

        $this->dispatcher->dispatchNow(new DownloadStarmapJob($this->option('force')));

        if ($this->option('import')) {
            $this->info('Starting Import');
            $this->dispatcher->dispatchNow(new ParseStarmapDownload());
        }

        return 0;
    }
}
