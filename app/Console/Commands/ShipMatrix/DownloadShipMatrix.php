<?php declare(strict_types = 1);

namespace App\Console\Commands\ShipMatrix;

use App\Jobs\Api\StarCitizen\Vehicle\DownloadShipMatrix as DownloadShipMatrixJob;
use App\Jobs\Api\StarCitizen\Vehicle\Parser\ParseShipMatrixDownload;
use Illuminate\Bus\Dispatcher;
use Illuminate\Console\Command;

/**
 * Start the Shipmatrix Download Job
 */
class DownloadShipMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:shipmatrix 
                            {--f|force : Force Download, Overwrite File if exist} 
                            {--i|import : Import Ships after Download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the Ship Matrix Download Job';

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
     * @return mixed
     */
    public function handle()
    {
        $this->info('Dispatching Ship Matrix Download');

        if ($this->option('force')) {
            $this->info('Forcing Download');
        }

        $this->dispatcher->dispatchNow(new DownloadShipMatrixJob($this->option('force')));

        if ($this->option('import')) {
            $this->info('Starting Import');
            $this->dispatcher->dispatchNow(new ParseShipMatrixDownload());
        }
    }
}
