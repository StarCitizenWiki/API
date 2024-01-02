<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizen\Starmap;

use App\Jobs\StarCitizen\Starmap\Download\DownloadStarmap as DownloadStarmapJob;
use App\Jobs\StarCitizen\Starmap\Import\ImportStarmap;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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

        $this->createDiskIfNotExists();

        $this->dispatcher->dispatch(new DownloadStarmapJob($this->option('force') === true));

        if ($this->option('import') === true) {
            $this->info('Starting Import');
            $this->dispatcher->dispatch(new ImportStarmap());
        }

        return 0;
    }

    /**
     * Create the starmap directory if it does not exist
     */
    private function createDiskIfNotExists(): void
    {
        if (!File::exists(config('filesystems.disks.starmap.root'))) {
            Storage::makeDirectory(config('filesystems.disks.starmap.root'));
        }
    }
}
